<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Poll;
use App\Models\Registration;
use App\Models\Role;
use App\Models\SignupField;
use App\Models\State;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_login_and_reach_admin_dashboard(): void
    {
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $user = User::updateOrCreate(['email' => 'admin@webinarly.test'], ['name' => 'Alex Morgan', 'password' => 'Webinar@123']);
        $user->roles()->sync([$role->id]);

        $this->post('/admin/login', ['login' => $user->email, 'password' => 'Webinar@123'])
            ->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_learner_cannot_open_admin_dashboard(): void
    {
        $role = Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner']);
        $user = User::updateOrCreate(['email' => 'learner@webinarly.test'], ['name' => 'John Anderson', 'password' => 'Webinar@123']);
        $user->roles()->sync([$role->id]);

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_new_registration_is_saved_as_a_learner_account(): void
    {
        $country = Country::where('iso2', 'IN')->firstOrFail();
        $state = State::where('country_id', $country->id)->where('name', 'Gujarat')->firstOrFail();
        $city = City::where('state_id', $state->id)->firstOrFail();
        $requiredField = SignupField::where('is_required', true)->first();
        $this->post('/register', [
            'name' => 'New Learner', 'email' => 'new.learner@example.com',
            'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123',
            'country_id' => $country->id, 'state_id' => $state->id, 'city_id' => $city->id,
            'custom' => $requiredField ? [$requiredField->id => $requiredField->options()->value('value')] : [],
        ])->assertRedirect(route('webinars.index'))
            ->assertSessionHas('auth_redirect', route('dashboard'));

        $this->assertDatabaseHas('users', ['name' => 'New Learner', 'email' => 'new.learner@example.com']);
        $this->assertDatabaseHas('role_user', ['user_id' => User::where('email', 'new.learner@example.com')->value('id')]);
    }

    public function test_learner_can_reserve_a_webinar_seat(): void
    {
        $learnerRole = Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner']);
        $adminRole = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $admin = User::updateOrCreate(['email' => 'admin2@example.com'], ['name' => 'Admin', 'password' => 'Webinar@123']);
        $admin->roles()->sync([$adminRole->id]);
        $user = User::updateOrCreate(['email' => 'seat@example.com'], ['name' => 'Seat Learner', 'password' => 'Webinar@123']);
        $user->roles()->sync([$learnerRole->id]);
        $webinar = Webinar::updateOrCreate(['slug' => 'test-webinar'], ['created_by' => $admin->id, 'title' => 'Test Webinar', 'auto_approve' => false]);
        $webinar->registrationForm()->updateOrCreate([], ['title' => 'Registration Form', 'is_active' => true, 'require_login' => true]);

        $this->actingAs($user)->post("/webinars/{$webinar->slug}/register")->assertRedirect();
        $this->assertDatabaseHas('registrations', ['webinar_id' => $webinar->id, 'user_id' => $user->id, 'status' => 'approved']);
    }

    public function test_hidden_country_and_state_use_india_and_gujarat_defaults(): void
    {
        $india = Country::where('iso2', 'IN')->firstOrFail();
        $gujarat = State::where('country_id', $india->id)->where('name', 'Gujarat')->firstOrFail();
        foreach (['registration_country_enabled' => '0', 'registration_state_enabled' => '0', 'registration_city_enabled' => '0', 'registration_default_country_id' => (string) $india->id, 'registration_default_state_id' => (string) $gujarat->id] as $key => $value) {
            DB::table('settings')->where('key', $key)->update(['value' => $value]);
        }
        $field = SignupField::where('is_required', true)->first();
        $this->post('/register', [
            'name' => 'Default Location', 'email' => 'default.location@example.com', 'password' => 'SecurePass123', 'password_confirmation' => 'SecurePass123',
            'custom' => $field ? [$field->id => $field->options()->value('value')] : [],
        ])->assertRedirect(route('webinars.index'))
            ->assertSessionHas('auth_redirect', route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'default.location@example.com', 'country_id' => $india->id, 'state_id' => $gujarat->id]);
    }

    public function test_profile_uses_and_updates_the_authenticated_user(): void
    {
        $role = Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner']);
        $user = User::updateOrCreate(['email' => 'profile@example.com'], ['name' => 'Real Profile Name', 'password' => 'Webinar@123']);
        $user->roles()->sync([$role->id]);

        $this->actingAs($user)->get('/profile')
            ->assertOk()
            ->assertSee('Real Profile Name')
            ->assertSee('profile@example.com');

        $this->actingAs($user)->put('/profile', [
            'name' => 'Updated Profile Name',
            'email' => 'updated.profile@example.com',
            'job_title' => 'Engineer',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Profile Name',
            'email' => 'updated.profile@example.com',
        ]);
    }

    public function test_admin_can_save_webinar_registration_fields(): void
    {
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $admin = User::updateOrCreate(['email' => 'webinar.admin@example.com'], ['name' => 'Webinar Admin', 'password' => 'Webinar@123']);
        $admin->roles()->sync([$role->id]);
        $response = $this->actingAs($admin)->post('/admin/webinars', [
            'title' => 'Dynamic Registration Webinar', 'status' => 'scheduled', 'language' => 'en', 'timezone' => 'Asia/Kolkata', 'registration_type' => 'free', 'registration_enabled' => '1',
            'fields' => [['label' => 'Industry', 'field_type' => 'dropdown', 'options' => "Technology\nHealthcare", 'is_enabled' => '1', 'is_required' => '1']],
            'polls_enabled' => '1', 'polls' => [['question' => 'Which topic?', 'options' => "AI\nCloud", 'status' => 'draft']],
            'certificate_enabled' => '1', 'certificate_name' => 'Completion Template', 'certificate_headline' => 'Certificate of Completion', 'certificate_signatory' => 'Program Director',
        ]);
        $response->assertRedirect();
        $webinar = Webinar::where('title', 'Dynamic Registration Webinar')->firstOrFail();
        $this->assertSame('dynamic-registration-webinar', $webinar->slug);
        $this->assertMatchesRegularExpression('/^dynamic-registration-webinar(?:-[a-z0-9]+)?$/', $webinar->slug);
        $this->assertTrue($webinar->registrationForm->is_active);
        $this->assertDatabaseHas('registration_fields', ['registration_form_id' => $webinar->registrationForm->id, 'label' => 'Industry', 'field_type' => 'dropdown', 'is_enabled' => 1]);
        $this->assertDatabaseCount('registration_field_options', 2);
        $this->assertDatabaseHas('polls', ['webinar_id' => $webinar->id, 'question' => 'Which topic?']);
        $this->assertDatabaseHas('certificate_templates', ['name' => 'Completion Template']);
    }

    public function test_learner_can_login_with_email_and_password(): void
    {
        $role = Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner']);
        $user = User::updateOrCreate(['email' => 'mobile.login@example.com'], ['name' => 'Mobile Learner', 'mobile' => '+919876543210', 'password' => 'Webinar@123']);
        $user->roles()->sync([$role->id]);
        $this->post('/login', ['login' => $user->email, 'password' => 'Webinar@123'])
            ->assertRedirect(route('webinars.index'))
            ->assertSessionHas('auth_redirect', route('dashboard'));
    }

    public function test_webinar_landing_page_always_shows_guest_login_button(): void
    {
        $adminRole = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $admin = User::updateOrCreate(['email' => 'landing.login.admin@example.com'], ['name' => 'Landing Admin', 'password' => 'Webinar@123']);
        $admin->roles()->sync([$adminRole->id]);
        $webinar = Webinar::updateOrCreate(['slug' => 'landing-login-test'], ['created_by' => $admin->id, 'title' => 'Landing Login Test', 'status' => 'scheduled']);

        $this->get(route('webinars.show', $webinar))->assertOk()->assertSee('Login');
    }

    public function test_learner_dashboard_engagement_and_certificate_actions_work(): void
    {
        $learnerRole = Role::firstOrCreate(['slug' => 'learner'], ['name' => 'Learner']);
        $adminRole = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin']);
        $admin = User::updateOrCreate(['email' => 'engagement.admin@example.com'], ['name' => 'Engagement Admin', 'password' => 'Webinar@123']);
        $admin->roles()->sync([$adminRole->id]);
        $learner = User::updateOrCreate(['email' => 'engagement.learner@example.com'], ['name' => 'Engagement Learner', 'password' => 'Webinar@123']);
        $learner->roles()->sync([$learnerRole->id]);
        $webinar = Webinar::updateOrCreate(['slug' => 'engagement-actions-test'], ['created_by' => $admin->id, 'title' => 'Engagement Actions', 'status' => 'live', 'chat_enabled' => true, 'polls_enabled' => true, 'comments_enabled' => true, 'feedback_enabled' => true, 'certificate_enabled' => 'yes']);
        Webinar::updateOrCreate(['slug' => 'not-my-webinar-test'], ['created_by' => $admin->id, 'title' => 'Not My Webinar', 'status' => 'scheduled']);
        Registration::updateOrCreate(['webinar_id' => $webinar->id, 'user_id' => $learner->id], ['email' => $learner->email, 'status' => 'approved', 'registered_at' => now()]);
        $poll = Poll::create(['webinar_id' => $webinar->id, 'created_by' => $admin->id, 'question' => 'Was this useful?', 'status' => 'active']);
        $option = $poll->options()->create(['label' => 'Yes', 'display_order' => 1]);
        $secondOption = $poll->options()->create(['label' => 'No', 'display_order' => 2]);

        $this->actingAs($learner)->post(route('webinars.chat.store', $webinar), ['message' => 'Hello everyone'])->assertRedirect();
        $this->actingAs($learner)->post(route('webinars.comments.store', $webinar), ['comment' => 'Excellent session'])->assertRedirect();
        $this->actingAs($learner)->post(route('webinars.feedback.store', $webinar), ['rating' => 5, 'message' => 'Very useful'])->assertRedirect();
        $this->actingAs($learner)->post(route('webinars.feedback.store', $webinar), ['rating' => 4])->assertRedirect();
        $this->actingAs($learner)->post(route('webinars.feedback.store', $webinar), ['message' => 'Message-only feedback works'])->assertRedirect();
        $this->actingAs($learner)->post(route('webinars.polls.vote', [$webinar, $poll]), ['option_id' => $option->id])->assertRedirect();
        $this->actingAs($learner)->post(route('webinars.polls.vote', [$webinar, $poll]), ['option_id' => $secondOption->id])->assertSessionHas('dashboard_toast_tone', 'warning');
        $this->assertDatabaseHas('chat_messages', ['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'message' => 'Hello everyone']);
        $this->assertDatabaseHas('comments', ['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'comment' => 'Excellent session']);
        $this->assertDatabaseHas('feedback', ['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'rating' => 4, 'message' => 'Message-only feedback works']);
        $this->assertDatabaseHas('poll_responses', ['poll_id' => $poll->id, 'poll_option_id' => $option->id, 'user_id' => $learner->id]);
        $this->assertDatabaseMissing('poll_responses', ['poll_id' => $poll->id, 'poll_option_id' => $secondOption->id, 'user_id' => $learner->id]);

        $this->actingAs($learner)->get(route('webinars.mine'))->assertOk()->assertSee('Engagement Actions')->assertDontSee('Not My Webinar')->assertSee('Attendance');

        DB::table('certificates')->insert(['webinar_id' => $webinar->id, 'user_id' => $learner->id, 'credential_id' => (string) Str::uuid(), 'status' => 'approved', 'issued_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($learner)->get(route('webinars.certificate.download', $webinar))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($learner)->get(route('certificates.index'))->assertOk()->assertSee('Engagement Actions')->assertDontSee('Future of AI in Business')->assertDontSee('Product Strategy 2026');
        $this->actingAs($learner)->get(route('recordings.index'))->assertOk()->assertSee('No recordings available')->assertDontSee('Building Scalable Digital Products');

        $this->withHeader('referer', route('webinars.dashboard', $webinar))->post(route('logout'))->assertRedirect('/'.$webinar->slug);
        $this->post('/login', ['login' => $learner->email, 'password' => 'Webinar@123', 'return_to' => '/'.$webinar->slug])
            ->assertRedirect(route('webinars.show', $webinar))
            ->assertSessionHas('auth_status')
            ->assertSessionHas('auth_redirect', route('webinars.dashboard', $webinar));
    }
}
