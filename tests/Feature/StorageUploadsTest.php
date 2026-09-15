<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageUploadsTest extends TestCase
{
    use DatabaseTransactions;

    private function getSuperAdmin(): User
    {
        return User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->firstOrFail();
    }

    private function getOrCreateWebinar(User $admin): Webinar
    {
        return Webinar::firstOrCreate(
            ['slug' => 'test-upload-webinar'],
            [
                'title' => 'Test Upload Webinar',
                'created_by' => $admin->id,
                'status' => 'scheduled',
            ]
        );
    }

    public function test_speaker_photo_is_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);

        $file = UploadedFile::fake()->image('speaker.jpg', 300, 300);

        $response = $this->actingAs($admin)->post('/admin/speakers', [
            'webinar_id' => $webinar->id,
            'name' => 'Dr. Storage Test',
            'headline' => 'Storage Specialist',
            'photo' => $file,
        ]);

        $response->assertRedirect('/admin/speakers');

        $speaker = Speaker::where('name', 'Dr. Storage Test')->firstOrFail();
        $this->assertStringStartsWith('/storage/speakers/', $speaker->photo_path);

        $relativePath = str_replace('/storage/', '', $speaker->photo_path);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_brand_logo_is_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);

        $file = UploadedFile::fake()->image('brand-logo.png', 200, 80);

        $response = $this->actingAs($admin)->post(route('admin.general.brands.store'), [
            'webinar_id' => $webinar->id,
            'name' => 'Acme Storage Corp',
            'logo' => $file,
        ]);

        $response->assertRedirect(route('admin.general.brands'));

        $brand = Brand::where('name', 'Acme Storage Corp')->firstOrFail();
        $this->assertStringStartsWith('/storage/brands/', $brand->logo_path);

        $relativePath = str_replace('/storage/', '', $brand->logo_path);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_banner_image_is_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);

        $file = UploadedFile::fake()->image('hero-banner.jpg', 1200, 400);

        $response = $this->actingAs($admin)->post(route('admin.general.banners.store'), [
            'webinar_id' => $webinar->id,
            'title' => 'Storage Hero Banner',
            'media_type' => 'image',
            'image_media' => $file,
        ]);

        $response->assertRedirect(route('admin.general.banners'));

        $banner = Banner::where('title', 'Storage Hero Banner')->firstOrFail();
        $this->assertStringStartsWith('/storage/banners/', $banner->media_path);

        $relativePath = str_replace('/storage/', '', $banner->media_path);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_site_settings_logo_is_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();

        $logoFile = UploadedFile::fake()->image('site-logo.png', 250, 70);

        $response = $this->actingAs($admin)->put(route('admin.general.site.update'), [
            'site_name' => 'Storage Test Platform',
            'footer_text' => 'Testing footer',
            'admin_email' => 'admin@test.test',
            'site_logo' => $logoFile,
        ]);

        $response->assertRedirect();

        $siteLogo = DB::table('settings')->where('key', 'site_logo')->value('value');
        $this->assertStringStartsWith('/storage/site/', $siteLogo);

        $relativePath = str_replace('/storage/', '', $siteLogo);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_webinar_resource_pdf_is_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);

        $file = UploadedFile::fake()->create('cheat-sheet.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)->put('/admin/webinars/' . $webinar->id, [
            'title' => $webinar->title,
            'slug' => $webinar->slug,
            'status' => $webinar->status,
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'early_entry_minutes' => 30,
            'registration_type' => 'free',
            'resource_pdfs' => [$file],
        ]);

        $response->assertRedirect();

        $resource = DB::table('webinar_resources')
            ->where('webinar_id', $webinar->id)
            ->where('title', 'cheat-sheet')
            ->first();

        $this->assertNotNull($resource);
        $this->assertStringStartsWith('/storage/resources/', $resource->path_or_url);

        $relativePath = str_replace('/storage/', '', $resource->path_or_url);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_chat_attachment_is_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);

        $file = UploadedFile::fake()->image('chat-screenshot.png', 400, 300);

        $response = $this->actingAs($admin)->postJson('/admin/chats/' . $webinar->id, [
            'message' => 'Here is the screenshot',
            'attachment' => $file,
        ]);

        $response->assertCreated()->assertJsonPath('message.attachment_name', 'chat-screenshot.png');

        $chat = DB::table('chat_messages')
            ->where('webinar_id', $webinar->id)
            ->where('message', 'Here is the screenshot')
            ->first();

        $this->assertNotNull($chat);
        $this->assertStringStartsWith('/storage/chat/', $chat->attachment_path);

        $relativePath = str_replace('/storage/', '', $chat->attachment_path);
        Storage::disk('public')->assertExists($relativePath);
    }

    public function test_certificate_template_and_signature_are_stored_in_storage_disk(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);

        $bg = UploadedFile::fake()->image('cert-bg.png', 1200, 850);
        $sig = UploadedFile::fake()->image('signature.png', 300, 100);

        $positions = [
            'recipient' => ['x' => 50, 'y' => 45, 'width' => 70, 'scale' => 100],
            'webinar' => ['x' => 50, 'y' => 55, 'width' => 70, 'scale' => 100],
            'date' => ['x' => 30, 'y' => 75, 'width' => 30, 'scale' => 100],
            'signature' => ['x' => 70, 'y' => 75, 'width' => 25, 'scale' => 100],
            'signatory' => ['x' => 70, 'y' => 85, 'width' => 30, 'scale' => 100],
        ];

        $response = $this->actingAs($admin)->put(route('admin.certificates.update', $webinar), [
            'name' => 'Storage Test Certificate',
            'orientation' => 'landscape',
            'headline' => 'Certificate of Participation',
            'signatory' => 'Dr. Director',
            'template_image' => $bg,
            'signature_image' => $sig,
            'positions' => $positions,
        ]);

        $response->assertRedirect();

        $webinar->refresh();
        $templateId = data_get($webinar->settings, 'certificate_template_id');
        $this->assertNotNull($templateId);

        $template = \App\Models\CertificateTemplate::findOrFail($templateId);
        $this->assertStringStartsWith('/storage/certificates/', $template->design['template_image']);
        $this->assertStringStartsWith('/storage/certificates/', $template->design['signature_image']);

        $bgRelative = str_replace('/storage/', '', $template->design['template_image']);
        $sigRelative = str_replace('/storage/', '', $template->design['signature_image']);

        Storage::disk('public')->assertExists($bgRelative);
        Storage::disk('public')->assertExists($sigRelative);
    }
}

