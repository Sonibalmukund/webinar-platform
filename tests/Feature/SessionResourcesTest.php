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

    public function test_admin_can_save_resources_and_learner_sees_them_in_room_and_landing(): void
    {
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = Webinar::create(['created_by' => $admin->id, 'title' => 'Resource Event', 'slug' => 'resource-'.uniqid(), 'status' => 'live', 'published_at' => now(), 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour(), 'timezone' => 'Asia/Kolkata', 'language' => 'en', 'registration_type' => 'free']);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);
        $payload = ['title' => $webinar->title, 'slug' => $webinar->slug, 'status' => 'live', 'language' => 'en', 'timezone' => 'Asia/Kolkata', 'starts_at' => now()->subHour()->format('Y-m-d H:i:s'), 'ends_at' => now()->addHour()->format('Y-m-d H:i:s'), 'early_entry_minutes' => 30, 'registration_type' => 'free', 'session_resources' => "Session guide | https://example.com/guide.pdf\nUnsafe | javascript:alert(1)"];
        $this->actingAs($admin)->put(route('admin.webinars.update', $webinar), $payload)->assertRedirect();
        $this->assertDatabaseHas('webinar_resources', ['webinar_id' => $webinar->id, 'title' => 'Session guide', 'path_or_url' => 'https://example.com/guide.pdf']);
        $this->assertSame(1, DB::table('webinar_resources')->where('webinar_id', $webinar->id)->count());
        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk()->assertSee('Session resources')->assertSee('Session guide');
        $this->get(route('webinars.show', $webinar))->assertOk()->assertSee('Session resources')->assertSee('Session guide');
    }
}
