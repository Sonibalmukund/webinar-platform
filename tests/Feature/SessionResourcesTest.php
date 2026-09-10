<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionResourcesTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['slug' => $role], ['name' => $role]));

        return $user;
    }

    public function test_admin_can_save_resources_and_learner_sees_them_in_room_and_not_on_landing(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = Webinar::create(['created_by' => $admin->id, 'title' => 'Resource Event', 'slug' => 'resource-'.uniqid(), 'status' => 'live', 'published_at' => now(), 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour(), 'timezone' => 'Asia/Kolkata', 'language' => 'en', 'registration_type' => 'free']);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $payload = ['contact_mobile' => '+91 98765 43210', 'title' => $webinar->title, 'slug' => $webinar->slug, 'status' => 'live', 'language' => 'en', 'timezone' => 'Asia/Kolkata', 'starts_at' => now()->subHour()->format('Y-m-d H:i:s'), 'ends_at' => now()->addHour()->format('Y-m-d H:i:s'), 'early_entry_minutes' => 30, 'registration_type' => 'free', 'session_resources' => "Session guide | https://example.com/guide.pdf\nUnsafe | javascript:alert(1)"];
        $this->actingAs($admin)->put(route('admin.webinars.update', $webinar), $payload)->assertRedirect();
        $this->assertDatabaseHas('webinar_resources', ['webinar_id' => $webinar->id, 'title' => 'Session guide', 'path_or_url' => 'https://example.com/guide.pdf']);
        $this->assertSame(1, DB::table('webinar_resources')->where('webinar_id', $webinar->id)->count());
        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk()->assertSee('Session resources')->assertSee('Session guide');
        $this->get(route('webinars.show', $webinar))->assertOk()->assertDontSee('Session resources')->assertDontSee('Session guide')->assertSee('+91 98765 43210')->assertDontSee('WEBINAR STATUS');
        DB::table('webinar_resources')->where('webinar_id', $webinar->id)->delete();
        $this->get(route('webinars.dashboard', $webinar))->assertOk()->assertDontSee('SESSION KIT')->assertDontSee('Resources (0)');
    }
    public function test_uploaded_resource_download_is_scoped_to_registered_webinar(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = Webinar::create(['created_by' => $admin->id, 'title' => 'Downloads', 'slug' => 'download-'.uniqid(), 'status' => 'live']);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $directory = public_path('uploads/resources');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($directory);
        $name = 'test-'.uniqid().'.pdf';
        file_put_contents($directory.'/'.$name, '%PDF-1.4 test resource');
        try {
            $id = DB::table('webinar_resources')->insertGetId(['webinar_id' => $webinar->id, 'uploaded_by' => $admin->id, 'title' => 'Session guide', 'type' => 'file', 'path_or_url' => '/uploads/resources/'.$name, 'is_public' => true]);
            $url = route('webinars.resources.download', [$webinar, $id]);
            $this->actingAs($learner)->get($url)->assertOk()->assertDownload('session-guide.pdf');
            $this->actingAs($this->user('learner'))->get($url)->assertForbidden();
            DB::table('webinar_resources')->where('id', $id)->update(['path_or_url' => '../.env']);
            $this->actingAs($learner)->get($url)->assertNotFound();
        } finally {
            unlink($directory.'/'.$name);
        }
    }

}
