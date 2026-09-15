<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePreferencesAndEarlyAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        $user = User::factory()->create(['password' => 'OldPassword123!']);
        $user->roles()->attach(Role::firstOrCreate(['slug' => $role], ['name' => $role]));

        return $user;
    }

    public function test_admin_profile_is_in_navigation_and_password_can_be_changed(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('class="active" href="'.route('admin.profile', [], false).'"', false)
            ->assertSee('Change password');

        $this->put(route('admin.password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPassword123!', $admin->fresh()->password));
    }

    public function test_webinar_form_has_language_and_timezone_dropdowns(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->get(route('admin.webinars.create'))
            ->assertOk()
            ->assertSee('id="webinarLanguage"', false)
            ->assertSee('Hindi (हिन्दी)')
            ->assertSee('id="webinarTimezone"', false)
            ->assertSee('Asia/Kolkata')
            ->assertSee('id="scheduleTimezonePreview"', false);
    }

    public function test_webinar_builder_uses_requested_iframe_layout_and_file_only_images(): void
    {
        $admin = $this->user('super-admin');
        $response = $this->actingAs($admin)->get(route('admin.webinars.create'));

        $response->assertOk()
            ->assertSee('stream-source-studio', false)
            ->assertSee('id="brandSvField"', false)
            ->assertSee('id="brandHueSlider"', false)
            ->assertSee('id="themeColorPickerModal"', false)
            ->assertSee('data-color-open="primary"', false)
            ->assertSee('id="experiencePreviewFrame"', false)
            ->assertSee('Resource Links')
            ->assertDontSee('Registration Template Preset')
            ->assertDontSee('Webinar Icon')
            ->assertDontSee('id="webinarIcon"', false)
            ->assertDontSee('name="registration_preset"', false)
            ->assertSee('Theater Mode')
            ->assertSee('Interactive Mode')
            ->assertDontSee('Interview Mode')
            ->assertDontSee('Panel Discussion')
            ->assertSee('name="brand_logo_file"', false)
            ->assertSee('name="waiting_media_file"', false)
            ->assertDontSee('name="brand_logo_url"', false)
            ->assertDontSee('name="waiting_media_url"', false)
            ->assertDontSee('name="certificate_min_attendance"', false);

        $this->actingAs($admin)->get(route('admin.general.banners.create'))
            ->assertOk()->assertSee('Choose banner image')->assertDontSee('Image URL');
        $this->actingAs($admin)->get(route('admin.general.brands.create'))
            ->assertOk()->assertSee('Choose brand logo')->assertDontSee('name="logo_url"', false);
    }

    public function test_live_controller_saves_certificate_watch_threshold(): void
    {
        $admin = $this->user('super-admin');
        $webinar = Webinar::create([
            'created_by' => $admin->id, 'title' => 'Controlled Event', 'slug' => 'controlled-'.uniqid(),
            'status' => 'live', 'settings' => ['experience' => ['certificate_min_attendance' => 80]],
        ]);

        $this->actingAs($admin)->get(route('admin.webinars.live', $webinar))
            ->assertOk()->assertSee('Certificate Minimum Watch Time (%)');

        $this->put(route('admin.webinars.controls', $webinar), [
            'status' => 'live', 'certificate_min_attendance' => 65,
        ])->assertRedirect(route('admin.webinars.index'));

        $this->assertSame(65, (int) data_get($webinar->fresh()->settings, 'experience.certificate_min_attendance'));
    }

    public function test_conditional_visibility_is_removed_and_legacy_fields_always_render(): void
    {
        $admin = $this->user('super-admin');
        $webinar = Webinar::create([
            'created_by' => $admin->id, 'title' => 'Conditional Form', 'slug' => 'conditional-'.uniqid(),
            'status' => 'scheduled', 'published_at' => now(),
        ]);
        $form = $webinar->registrationForm()->create(['title' => 'Register', 'is_active' => true]);
        $parent = $form->fields()->create(['label' => 'Need invoice?', 'field_key' => 'need_invoice', 'field_type' => 'dropdown', 'is_enabled' => true, 'display_order' => 1]);
        $parent->options()->create(['label' => 'Yes', 'value' => 'yes', 'is_enabled' => true, 'display_order' => 1]);
        $form->fields()->create([
            'label' => 'GST Number', 'field_key' => 'gst_number', 'field_type' => 'text', 'is_enabled' => true,
            'condition_field_id' => $parent->id, 'condition_operator' => 'equals', 'condition_value' => 'yes', 'display_order' => 2,
        ]);

        $this->get(route('webinars.show', $webinar))->assertOk()
            ->assertSee('GST Number')
            ->assertDontSee('data-conditional-field', false)
            ->assertDontSee('data-condition-field=', false);

        $this->actingAs($admin)->get(route('admin.registration.webinar.fields.edit', $form->fields->last()))
            ->assertOk()->assertDontSee('Conditional visibility');
    }

    public function test_selected_timezone_is_used_when_saving_schedule(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->post(route('admin.webinars.store'), [
            'title' => 'New York Schedule',
            'status' => 'scheduled',
            'language' => 'es',
            'timezone' => 'America/New_York',
            'starts_at' => '2026-09-20T10:00',
            'ends_at' => '2026-09-20T11:30',
            'early_entry_minutes' => 15,
            'registration_type' => 'free',
        ])->assertRedirect(route('admin.webinars.index'));

        $webinar = Webinar::where('title', 'New York Schedule')->firstOrFail();
        $this->assertSame('es', $webinar->language);
        $this->assertSame('America/New_York', $webinar->timezone);
        $this->assertSame(15, (int) $webinar->early_entry_minutes);
        $this->assertSame('2026-09-20 14:00', $webinar->starts_at->utc()->format('Y-m-d H:i'));
    }

    public function test_registered_attendee_can_only_enter_during_early_access_window(): void
    {
        Carbon::setTestNow('2026-09-13 09:00:00');
        $admin = $this->user('super-admin');
        $learner = $this->user('learner');
        $webinar = Webinar::create([
            'created_by' => $admin->id,
            'title' => 'Early Access Test',
            'slug' => 'early-access-'.uniqid(),
            'status' => 'scheduled',
            'language' => 'hi',
            'timezone' => 'Asia/Kolkata',
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
            'early_entry_minutes' => 30,
        ]);
        Registration::create([
            'webinar_id' => $webinar->id,
            'user_id' => $learner->id,
            'email' => $learner->email,
            'status' => 'approved',
        ]);

        $this->actingAs($learner)->get(route('webinars.dashboard', $webinar))->assertForbidden();
        $this->postJson(route('webinars.attendance.join', $webinar))->assertForbidden();

        Carbon::setTestNow('2026-09-13 09:30:00');
        $this->get(route('webinars.dashboard', $webinar))->assertOk();
        $this->postJson(route('webinars.attendance.join', $webinar))->assertOk();

        Carbon::setTestNow();
    }

    public function test_zero_early_access_does_not_fall_back_to_thirty_minutes(): void
    {
        Carbon::setTestNow('2026-09-13 09:30:00');
        $admin = $this->user('super-admin');
        $webinar = Webinar::create([
            'created_by' => $admin->id,
            'title' => 'No Early Access',
            'slug' => 'no-early-access-'.uniqid(),
            'status' => 'scheduled',
            'starts_at' => now()->addMinutes(30),
            'ends_at' => now()->addHours(2),
            'early_entry_minutes' => 0,
        ]);

        $this->assertTrue($webinar->opensAt()->equalTo($webinar->starts_at));
        $this->assertFalse($webinar->canEnter());

        Carbon::setTestNow();
    }
}
