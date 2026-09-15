<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LearnerSimplifiedFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->sync([Role::firstOrCreate(['slug' => $role], ['name' => str($role)->headline()])->id]);

        return $user;
    }

    public function test_learner_login_is_passwordless_and_redirects_directly_to_dashboard(): void
    {
        $learner = $this->user('learner');

        $this->get('/webinars')->assertOk()
            ->assertDontSee('id="frontendPassword"', false)
            ->assertDontSee('id="regPassword"', false);
        $this->post('/login', ['login' => $learner->email])
            ->assertRedirect(route('dashboard'))
            ->assertSessionMissing('auth_redirect');
        $this->assertAuthenticatedAs($learner);
    }

    public function test_global_registration_needs_no_password_and_redirects_directly_to_dashboard(): void
    {
        foreach ([
            'registration_mobile_enabled', 'registration_country_enabled',
            'registration_state_enabled', 'registration_city_enabled',
        ] as $key) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['group' => 'registration', 'value' => '0', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('settings')->updateOrInsert(['key' => 'registration_email_enabled'], ['group' => 'registration', 'value' => '1', 'is_public' => false, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('signup_fields')->update(['is_enabled' => false]);

        $this->post('/register', ['name' => 'Passwordless Learner', 'email' => 'passwordless@example.test'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionMissing('auth_redirect');
        $this->assertDatabaseHas('users', ['email' => 'passwordless@example.test']);
    }

    public function test_early_room_access_does_not_create_attendance_until_start_time(): void
    {
        Carbon::setTestNow('2026-09-13 10:00:00');
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = Webinar::create([
            'created_by' => $admin->id, 'title' => 'Waiting Room Attendance',
            'slug' => 'waiting-room-attendance-'.uniqid(), 'status' => 'scheduled',
            'starts_at' => now()->addMinutes(20), 'ends_at' => now()->addHour(),
            'early_entry_minutes' => 30,
        ]);
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'email' => $learner->email, 'status' => 'approved']);

        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertOk();
        $this->postJson(route('webinars.attendance.join', $webinar))
            ->assertOk()->assertJsonPath('tracking_started', false)->assertJsonPath('state', 'waiting');
        $this->assertDatabaseMissing('webinar_attendees', ['webinar_id' => $webinar->id, 'user_id' => $learner->id]);

        Carbon::setTestNow('2026-09-13 10:20:00');
        $this->postJson(route('webinars.attendance.join', $webinar))
            ->assertOk()->assertJsonPath('tracking_started', true)->assertJsonPath('state', 'join');
        $this->assertDatabaseHas('webinar_attendees', ['webinar_id' => $webinar->id, 'user_id' => $learner->id]);
        Carbon::setTestNow();
    }

    public function test_admin_webinar_wizard_contains_dynamic_fields_and_correct_answer_dropdown(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->get(route('admin.webinars.create'))
            ->assertOk()
            ->assertSee('5. Dynamic Fields')
            ->assertSee('name="registration_enabled"', false)
            ->assertSee('id="pollCorrectIndex"', false)
            ->assertDontSee('name="poll_correct_index" value=', false);
    }

    public function test_admin_public_webinar_link_uses_same_tab_and_does_not_auto_open_registration(): void
    {
        $admin = $this->user('super-admin');
        $webinar = Webinar::create([
            'created_by' => $admin->id, 'title' => 'Same Tab Preview',
            'slug' => 'same-tab-preview-'.uniqid(), 'status' => 'scheduled',
            'starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2),
        ]);
        $publicUrl = route('webinars.show', $webinar);

        $this->actingAs($admin)->get(route('admin.webinars.index'))
            ->assertOk()
            ->assertSee('<a class="url-action open" href="'.$publicUrl.'">', false)
            ->assertDontSee('<a class="url-action open" href="'.$publicUrl.'" target="_blank">', false);

        $this->get($publicUrl)->assertOk()
            ->assertSee('Admin Preview · Edit')
            ->assertDontSee('id="micrositeRegisterModal"', false)
            ->assertDontSee('Complete Registration');
    }
}
