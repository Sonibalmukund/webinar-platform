<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\CertificateTemplate;
use App\Models\Registration;
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

    public function test_editing_a_shared_certificate_only_updates_the_selected_webinar_and_existing_downloads(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $first = $this->getOrCreateWebinar($admin);
        $second = Webinar::create([
            'title' => 'Second Certificate Webinar',
            'slug' => 'second-certificate-webinar',
            'created_by' => $admin->id,
            'status' => 'draft',
        ]);
        $shared = CertificateTemplate::create([
            'name' => 'Shared Certificate',
            'orientation' => 'landscape',
            'design' => ['headline' => 'Original headline'],
            'created_by' => $admin->id,
        ]);
        foreach ([$first, $second] as $webinar) {
            $webinar->update(['settings' => ['certificate_template_id' => $shared->id], 'certificate_enabled' => 'yes']);
        }
        DB::table('certificates')->insert([
            'webinar_id' => $first->id,
            'user_id' => $admin->id,
            'template_id' => $shared->id,
            'credential_id' => (string) \Illuminate\Support\Str::uuid(),
            'status' => 'approved',
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $positions = [
            'recipient' => ['x' => 50, 'y' => 44, 'width' => 55, 'scale' => 100],
            'webinar' => ['x' => 50, 'y' => 61, 'width' => 55, 'scale' => 100],
            'date' => ['x' => 20, 'y' => 84, 'width' => 25, 'scale' => 100],
            'signature' => ['x' => 80, 'y' => 76, 'width' => 22, 'scale' => 100],
            'signatory' => ['x' => 80, 'y' => 86, 'width' => 30, 'scale' => 100],
        ];
        $this->actingAs($admin)->put(route('admin.certificates.update', $first), [
            'name' => 'First Webinar Certificate',
            'orientation' => 'portrait',
            'headline' => 'Updated only here',
            'template_image' => UploadedFile::fake()->image('portrait.png', 800, 1200),
            'positions' => $positions,
        ])->assertRedirect();

        $firstTemplateId = (int) data_get($first->fresh()->settings, 'certificate_template_id');
        $this->assertNotSame($shared->id, $firstTemplateId);
        $this->assertSame($shared->id, (int) data_get($second->fresh()->settings, 'certificate_template_id'));
        $this->assertSame('Original headline', data_get($shared->fresh()->design, 'headline'));
        $this->assertEqualsWithDelta(0.666667, (float) data_get(CertificateTemplate::findOrFail($firstTemplateId)->design, 'canvas_aspect_ratio'), 0.00001);
        $this->assertSame($firstTemplateId, (int) DB::table('certificates')->where('webinar_id', $first->id)->value('template_id'));
    }

    public function test_webinar_editor_saves_changes_to_an_existing_certificate_template(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $webinar = $this->getOrCreateWebinar($admin);
        $template = CertificateTemplate::create([
            'name' => 'Wizard Template',
            'orientation' => 'landscape',
            'design' => ['headline' => 'Old headline'],
            'created_by' => $admin->id,
        ]);
        $webinar->update(['settings' => ['certificate_template_id' => $template->id], 'certificate_enabled' => 'yes']);
        $positions = [
            'recipient' => ['x' => 50, 'y' => 44, 'width' => 55, 'scale' => 100],
            'webinar' => ['x' => 50, 'y' => 61, 'width' => 55, 'scale' => 100],
            'date' => ['x' => 20, 'y' => 84, 'width' => 25, 'scale' => 100],
            'signature' => ['x' => 80, 'y' => 76, 'width' => 22, 'scale' => 100],
            'signatory' => ['x' => 80, 'y' => 86, 'width' => 30, 'scale' => 100],
        ];

        $this->actingAs($admin)->put(route('admin.webinars.update', $webinar), [
            'title' => $webinar->title,
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'brand_logo_file' => UploadedFile::fake()->image('client-logo.png', 300, 100),
            'certificate_enabled' => '1',
            'certificate_template_id' => $template->id,
            'certificate_name' => 'Wizard Template Updated',
            'certificate_headline' => 'Updated from webinar editor',
            'certificate_orientation' => 'portrait',
            'certificate_template_image' => UploadedFile::fake()->image('wizard-portrait.png', 900, 1200),
            'positions' => $positions,
        ])->assertRedirect(route('admin.webinars.index'));

        $saved = CertificateTemplate::findOrFail(data_get($webinar->fresh()->settings, 'certificate_template_id'));
        $this->assertSame('Wizard Template Updated', $saved->name);
        $this->assertSame('portrait', $saved->orientation);
        $this->assertSame('Updated from webinar editor', data_get($saved->design, 'headline'));
        $this->assertEqualsWithDelta(0.75, (float) data_get($saved->design, 'canvas_aspect_ratio'), 0.00001);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', data_get($saved->design, 'template_image')));
    }

    public function test_authenticated_registered_user_downloads_the_saved_background_template(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();
        $user = User::whereHas('roles', fn ($query) => $query->where('slug', 'learner'))->firstOrFail();
        $webinar = $this->getOrCreateWebinar($admin);
        $path = UploadedFile::fake()->image('download-bg.jpg', 1200, 800)->storeAs('certificates', 'download-bg.jpg', 'public');
        $template = CertificateTemplate::create([
            'name' => 'INTERNAL-NAME-MUST-NOT-PRINT',
            'orientation' => 'landscape',
            'design' => [
                'template_image' => '/storage/'.$path,
                'canvas_aspect_ratio' => 1.5,
                'positions' => [],
                'visible_elements' => [
                    'headline' => false,
                    'recipient' => true,
                    'webinar' => false,
                    'date' => false,
                    'signature' => false,
                    'signatory' => false,
                ],
            ],
            'created_by' => $admin->id,
        ]);
        $webinar->update([
            'certificate_enabled' => 'yes',
            'settings' => ['certificate_template_id' => $template->id],
        ]);
        Registration::updateOrCreate(
            ['webinar_id' => $webinar->id, 'email' => $user->email],
            ['user_id' => $user->id, 'status' => 'approved', 'approved_at' => now()]
        );
        DB::table('certificates')->updateOrInsert(
            ['webinar_id' => $webinar->id, 'user_id' => $user->id],
            [
                'template_id' => $template->id,
                'credential_id' => (string) \Illuminate\Support\Str::uuid(),
                'status' => 'approved',
                'issued_at' => now(),
                'revoked_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $response = $this->actingAs($user)->get(route('webinars.certificate.download', $webinar));

        $this->assertSame(200, $response->status(), $response->getContent());
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('/Subtype /Image', $response->getContent());
        $this->assertStringContainsString($user->name, $response->getContent());
        $this->assertStringNotContainsString($webinar->title, $response->getContent());
        $this->assertStringNotContainsString('INTERNAL-NAME-MUST-NOT-PRINT', $response->getContent());
    }
}
