<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Registration;
use App\Models\Role;
use App\Models\State;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FrontendAuthFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function webinar(): Webinar
    {
        $owner = User::factory()->create();

        return Webinar::create(['created_by' => $owner->id, 'slug' => 'popup-flow-event', 'title' => 'Popup Flow Event', 'status' => 'scheduled']);
    }

    private function learner(): User
    {
        $user = User::factory()->create(['password' => 'PopupPass123']);
        $user->roles()->sync([Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner'])->id]);

        return $user;
    }

    public function test_legacy_frontend_auth_urls_redirect_to_popups(): void
    {
        foreach (['login' => 'login', 'register' => 'register', 'forgot-password' => 'forgot'] as $path => $modal) {
            $this->get('/'.$path)->assertRedirect('/webinars?auth='.$modal);
        }
        $this->get('/webinars')->assertOk()->assertSee('id="micrositeLoginModal"', false)->assertSee('id="micrositeRegisterModal"', false);
    }

    public function test_event_auth_links_and_expired_session_keep_event_context(): void
    {
        $webinar = $this->webinar();
        $this->get('/login?return_to=/'.$webinar->slug.'/dashboard')->assertRedirect('/'.$webinar->slug.'?auth=login');
        $this->get('/register?return_to=/'.$webinar->slug)->assertRedirect('/'.$webinar->slug.'?auth=register');
        $this->get('/'.$webinar->slug.'/dashboard')->assertRedirect('/'.$webinar->slug.'?auth=login');
    }

    public function test_admin_and_sub_admin_share_one_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/dashboard')->assertRedirect(route('admin.login'));
        $this->get('/sub-admin/dashboard')->assertRedirect('/admin/dashboard');
        $this->get('/admin/login')->assertOk()->assertSee('Sign in securely');
        $this->get('/sub-admin/login')->assertRedirect('/admin/login');

        $this->actingAs($this->learner());
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('Sign in securely');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']));
        $this->actingAs($admin)->withSession(['auth_status' => 'Login successfully.'])->get(route('admin.dashboard'))
            ->assertOk()->assertSee('data-app-flash="Login successfully."', false);
    }

    public function test_same_browser_session_can_access_multiple_registered_webinars(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')
            ->where('group', 'registration')
            ->where('key', 'registration_password_enabled')
            ->update(['value' => '1']);

        $first = $this->webinar();
        $second = Webinar::create([
            'created_by' => $first->created_by,
            'slug' => 'second-popup-flow-event',
            'title' => 'Second Popup Flow Event',
            'status' => 'scheduled',
        ]);
        $user = $this->learner();
        foreach ([$first, $second] as $webinar) {
            Registration::create(['webinar_id' => $webinar->id, 'user_id' => $user->id, 'email' => $user->email, 'status' => 'approved']);
        }

        foreach ([$first, $second] as $webinar) {
            $this->post('/login', [
                'return_to' => '/'.$webinar->slug,
                'webinar_id' => $webinar->id,
                'login' => $user->email,
                'password' => 'PopupPass123',
            ])->assertRedirect(route('webinars.show', $webinar))
                ->assertSessionHas('auth_redirect', route('webinars.dashboard', $webinar));

            $this->assertAuthenticatedAs($user);
            $this->assertDatabaseHas('registrations', [
                'webinar_id' => $webinar->id,
                'user_id' => $user->id,
            ]);
        }

        $this->get(route('webinars.dashboard', $first))->assertOk();
        $this->get(route('webinars.dashboard', $second))->assertOk();
    }

    public function test_event_login_does_not_register_or_open_an_unregistered_webinar(): void
    {
        $webinar = $this->webinar();
        $user = $this->learner();

        $this->post('/login', [
            'return_to' => '/'.$webinar->slug,
            'webinar_id' => $webinar->id,
            'login' => $user->email,
            'password' => 'PopupPass123',
        ])->assertRedirect('/'.$webinar->slug.'?auth=login')
            ->assertSessionHasErrors('login')
            ->assertSessionMissing('auth_redirect');

        $this->assertDatabaseMissing('registrations', ['webinar_id' => $webinar->id, 'user_id' => $user->id]);
        $this->actingAs($user)->get(route('webinars.dashboard', $webinar))->assertForbidden();
    }

    public function test_event_without_custom_login_field_still_has_both_popups(): void
    {
        \Illuminate\Support\Facades\DB::table('settings')->where('key', 'registration_password_enabled')->update(['value' => '0']);
        $webinar = $this->webinar();
        $this->get('/'.$webinar->slug)->assertOk()
            ->assertSee('id="micrositeLoginModal"', false)
            ->assertSee('id="micrositeRegisterModal"', false)
            ->assertSee('Email address')
            ->assertDontSee('id="frontendPassword"', false)
            ->assertDontSee('EVENT PROGRAM')
            ->assertDontSee('href="/login"', false);
    }

    public function test_bad_login_and_registration_errors_return_to_event_popup(): void
    {
        $webinar = $this->webinar();
        $this->post('/login', ['return_to' => '/'.$webinar->slug, 'login' => 'unknown@example.test', 'password' => 'incorrect'])
            ->assertRedirect('/'.$webinar->slug.'?auth=login')->assertSessionHasErrors('login')->assertSessionHas('_old_input._auth_modal', 'login');
        $this->post('/register', ['return_to' => '/'.$webinar->slug, 'webinar_id' => $webinar->id])
            ->assertRedirect('/'.$webinar->slug.'?auth=register')->assertSessionHasErrors('name')->assertSessionHas('_old_input._auth_modal', 'register');
    }

    public function test_event_login_returns_to_dashboard_with_success_toast(): void
    {
        $webinar = $this->webinar();
        $user = $this->learner();
        Registration::create(['webinar_id' => $webinar->id, 'user_id' => $user->id, 'email' => $user->email, 'status' => 'approved']);
        $this->post('/login', ['return_to' => '/'.$webinar->slug, 'webinar_id' => $webinar->id, 'login' => $user->email, 'password' => 'PopupPass123'])
            ->assertRedirect(route('webinars.show', $webinar))
            ->assertSessionHas('auth_status')
            ->assertSessionHas('auth_redirect', route('webinars.dashboard', $webinar));
        $this->actingAs($user)->withSession(['auth_status' => 'Login successfully.'])->get(route('webinars.dashboard', $webinar))
            ->assertOk()->assertSee('id="appToast"', false)->assertSee('data-app-flash', false);
        $this->actingAs($user)->get(route('webinars.dashboard', $webinar))->assertOk()->assertDontSee('id="appToast"', false);
    }

    public function test_logout_uses_explicit_event_without_a_referer(): void
    {
        $webinar = $this->webinar();
        $this->actingAs($this->learner())->post('/logout', ['return_to' => '/'.$webinar->slug.'/dashboard'])
            ->assertRedirect('/'.$webinar->slug);
        $this->assertGuest();
    }

    public function test_logout_from_portal_remembers_the_last_event(): void
    {
        $webinar = $this->webinar();
        $this->actingAs($this->learner())->withSession(['frontend_event_slug' => $webinar->slug])->post('/logout')
            ->assertRedirect('/'.$webinar->slug);
    }

    public function test_external_return_urls_are_never_followed(): void
    {
        $this->get('/login?return_to=https://outside.example/path')->assertRedirect('/webinars?auth=login');
        $this->actingAs($this->learner())->post('/logout', ['return_to' => '//outside.example/login'])->assertRedirect('/webinars');
    }

    public function test_registration_from_popup_creates_event_seat_and_opens_dashboard(): void
    {
        $webinar = Webinar::where('slug', 'future-of-digital-healthcare-2026')->firstOrFail();
        $webinar->update(['auto_approve' => false]);
        $country = Country::where('iso2', 'IN')->firstOrFail();
        $state = State::where('country_id', $country->id)->where('name', 'Gujarat')->firstOrFail();
        $city = City::where('state_id', $state->id)->firstOrFail();
        $fields = $webinar->registrationForm->fields->where('is_enabled', true)->reject(fn ($field) => in_array($field->field_key, ['full_name', 'email', 'mobile', 'city']))
            ->mapWithKeys(fn ($field) => [$field->id => $field->options()->value('value') ?: 'Test response'])->all();
        $this->post('/register', ['webinar_id' => $webinar->id, 'return_to' => '/'.$webinar->slug,
            'name' => 'Popup Test Attendee', 'email' => 'popup-registration@example.test', 'password' => 'PopupPass123', 'password_confirmation' => 'PopupPass123',
            'country_id' => $country->id, 'state_id' => $state->id, 'city_id' => $city->id, 'fields' => $fields,
        ])->assertRedirect(route('webinars.show', $webinar))
            ->assertSessionHas('registration_status')
            ->assertSessionHas('auth_redirect', route('webinars.dashboard', $webinar));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('registrations', ['webinar_id' => $webinar->id, 'email' => 'popup-registration@example.test', 'status' => 'approved']);
        $this->post('/logout', ['return_to' => '/'.$webinar->slug])->assertRedirect('/'.$webinar->slug);
    }
}
