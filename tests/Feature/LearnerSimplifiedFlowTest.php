<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
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

    public function test_attendance_starts_when_early_room_access_opens(): void
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
        $this->assertNotNull(DB::table('webinar_attendees')->where(['webinar_id' => $webinar->id, 'user_id' => $learner->id])->value('joined_at'));
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
            ->assertSee('3. Registration &amp; Publish', false)
            ->assertSee('name="registration_enabled"', false)
            ->assertSee('data-certificate-visibility="headline"', false)
            ->assertSee('role="switch" name="certificate_visible_elements[headline]"', false)
            ->assertSee('type="hidden" id="certificateOrientation" name="certificate_orientation"', false)
            ->assertDontSee('<span>Orientation</span>', false)
            ->assertSee('data-certificate-field="headline"', false)
            ->assertSee('data-certificate-designer-only', false)
            ->assertSee('Upload a certificate background to customize its content and open the live preview.')
            ->assertSee('Add another speaker')
            ->assertDontSee('Select existing speakers')
            ->assertSee('Maximum 10 MB each.')
            ->assertSee('Advanced element positioning')
            ->assertSee('Attendee name (automatic)')
            ->assertSee('Attendee preview name')
            ->assertSee('id="certificateBold"', false)
            ->assertSee('data-position-bold="recipient"', false)
            ->assertSee('Horizontal (X %)')
            ->assertSee('Vertical (Y %)')
            ->assertDontSee('Drag to reposition or edit X/Y inputs')
            ->assertDontSee('Save changes')
            ->assertSee('Add another poll')
            ->assertSee('data-poll-correct', false)
            ->assertSee('Correct answer visibility')
            ->assertDontSee('name="poll_correct_index" value=', false);
    }

    public function test_admin_can_create_multiple_polls_from_webinar_wizard(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->post(route('admin.webinars.store'), [
            'title' => 'Multiple Poll Webinar',
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'registration_type' => 'free',
            'polls_enabled' => '1',
            'new_polls' => [
                ['question' => 'First question?', 'answers' => ['One', 'Two'], 'correct_index' => '1', 'answer_reveal' => 'immediate', 'status' => 'draft', 'allow_multiple' => '0'],
                ['question' => 'Second question?', 'answers' => ['Alpha', 'Beta'], 'correct_index' => '0', 'answer_reveal' => 'after_webinar', 'status' => 'draft', 'allow_multiple' => '0'],
            ],
        ])->assertRedirect(route('admin.webinars.index'));

        $webinar = Webinar::where('title', 'Multiple Poll Webinar')->firstOrFail();
        $this->assertDatabaseHas('polls', ['webinar_id' => $webinar->id, 'question' => 'First question?', 'answer_reveal' => 'immediate']);
        $this->assertDatabaseHas('polls', ['webinar_id' => $webinar->id, 'question' => 'Second question?', 'answer_reveal' => 'after_webinar']);
        $this->assertDatabaseHas('poll_options', ['label' => 'Two', 'is_correct' => true]);
        $this->assertDatabaseHas('poll_options', ['label' => 'Alpha', 'is_correct' => true]);
    }

    public function test_webinar_builder_saves_multiple_speakers_and_requires_a_brand_name(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->post(route('admin.webinars.store'), [
            'title' => 'Speaker Builder Webinar',
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'registration_type' => 'free',
            'branding_assets_present' => '1',
            'speakers' => [
                ['name' => 'First Speaker', 'headline' => 'Host', 'company' => 'Acme'],
                ['name' => 'Second Speaker', 'headline' => 'Guest', 'company' => 'Beta'],
            ],
        ])->assertRedirect(route('admin.webinars.index'));

        $webinar = Webinar::where('title', 'Speaker Builder Webinar')->firstOrFail();
        $this->assertSame(2, $webinar->speakers()->count());
        $this->assertDatabaseHas('speakers', ['name' => 'First Speaker', 'company' => 'Acme']);
        $this->assertDatabaseHas('speakers', ['name' => 'Second Speaker', 'company' => 'Beta']);

        $this->actingAs($admin)->from(route('admin.webinars.create'))->post(route('admin.webinars.store'), [
            'title' => 'Brand Validation Webinar',
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'registration_type' => 'free',
            'branding_assets_present' => '1',
            'brands' => [['name' => '', 'logo_url' => 'https://example.com/logo.png']],
        ])->assertRedirect(route('admin.webinars.create'))->assertSessionHasErrors('brands.0.name');
    }

    public function test_dynamic_banner_keys_are_never_used_as_database_display_order(): void
    {
        $admin = $this->user('super-admin');

        $this->actingAs($admin)->post(route('admin.webinars.store'), [
            'title' => 'Safe Banner Ordering',
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'registration_type' => 'free',
            'branding_assets_present' => '1',
            'banners' => [
                1790327030429 => [
                    'title' => 'Landing image',
                    'media_type' => 'image',
                    'media_url' => 'https://example.com/banner.png',
                ],
            ],
        ])->assertRedirect(route('admin.webinars.index'));

        $webinar = Webinar::where('title', 'Safe Banner Ordering')->firstOrFail();
        $this->assertDatabaseHas('banners', [
            'webinar_id' => $webinar->id,
            'title' => 'Landing image',
            'display_order' => 0,
        ]);
    }

    public function test_existing_brand_and_banner_can_be_removed_from_the_webinar_builder(): void
    {
        $admin = $this->user('super-admin');
        $webinar = Webinar::create([
            'created_by' => $admin->id,
            'title' => 'Removable Branding',
            'slug' => 'removable-branding-'.uniqid(),
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'registration_type' => 'free',
        ]);
        $brand = Brand::create(['webinar_id' => $webinar->id, 'name' => 'Old Brand', 'logo_path' => '/old-brand.png', 'is_active' => true, 'display_order' => 0]);
        $banner = Banner::create(['webinar_id' => $webinar->id, 'title' => 'Old Banner', 'media_type' => 'image', 'media_path' => '/old-banner.png', 'is_active' => true, 'display_order' => 0]);

        $this->actingAs($admin)->put(route('admin.webinars.update', $webinar), [
            'title' => $webinar->title,
            'status' => 'draft',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'registration_type' => 'free',
            'branding_assets_present' => '1',
            'remove_brand_ids' => [$brand->id],
            'remove_banner_ids' => [$banner->id],
            'brands' => [['id' => $brand->id, 'name' => $brand->name]],
            'banners' => [['id' => $banner->id, 'title' => $banner->title, 'media_type' => 'image', 'media_url' => '']],
        ])->assertRedirect(route('admin.webinars.index'));

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
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
