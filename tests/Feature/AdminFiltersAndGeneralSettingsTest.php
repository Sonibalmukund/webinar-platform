<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminFiltersAndGeneralSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private function getSuperAdmin(): User
    {
        $role = Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin', 'is_system' => true]);
        $admin = User::firstOrCreate(
            ['email' => 'admin@webinarly.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'status' => 'active']
        );
        $admin->roles()->syncWithoutDetaching([$role->id]);

        return $admin;
    }

    public function test_admin_general_settings_navigation_and_pages(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)
            ->get('/admin/general-settings/banners')
            ->assertOk()
            ->assertSee('sidebar-uploaded-brand', false)
            ->assertSee('Banners')
            ->assertSee('Brands')
            ->assertSee('Site Settings')
            ->assertSee('Speakers')
            ->assertSee('Search banners...');

        $this->actingAs($admin)
            ->get('/admin/general-settings/brands')
            ->assertOk()
            ->assertSee('Banners')
            ->assertSee('Brands')
            ->assertSee('Site Settings')
            ->assertSee('Speakers')
            ->assertSee('Search brands...');

        $this->actingAs($admin)
            ->get('/admin/speakers')
            ->assertOk()
            ->assertSee('Banners')
            ->assertSee('Brands')
            ->assertSee('Site Settings')
            ->assertSee('Speakers');
    }

    public function test_admin_users_and_registrations_filters(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)
            ->get('/admin/users?search=test&status=active')
            ->assertOk()
            ->assertSee('Search users by name')
            ->assertSee('All webinars')
            ->assertSee('All statuses')
            ->assertSee('Filter')
            ->assertSee('Reset')
            ->assertDontSee($admin->email); // Staff excluded from table

        $this->actingAs($admin)
            ->get('/admin/registrations?status=approved')
            ->assertOk()
            ->assertSee('Registrations')
            ->assertSee('Search attendees by name, email, phone...')
            ->assertSee('Filter');
    }

    public function test_admin_attendance_and_polls_filters(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)
            ->get('/admin/attendance?search=attendee')
            ->assertOk()
            ->assertSee('Search attendees by name, email, phone...')
            ->assertSee('All webinars')
            ->assertSee('Filter');

        $this->actingAs($admin)
            ->get('/admin/polls?search=impact')
            ->assertOk()
            ->assertSee('Search polls by question...')
            ->assertSee('All webinars')
            ->assertSee('Filter');
    }

    public function test_attendance_displays_webinar_local_time_without_requiring_live_status(): void
    {
        $admin = $this->getSuperAdmin();
        $learner = User::factory()->create(['name' => 'Timezone Attendee']);
        $base = ['created_by' => $admin->id, 'starts_at' => '2026-09-13 05:00:00', 'ends_at' => '2026-09-13 07:00:00', 'timezone' => 'Asia/Kolkata'];
        $live = Webinar::create($base + ['title' => 'Visible Live Webinar', 'slug' => 'visible-live-'.uniqid(), 'status' => 'live']);
        $scheduled = Webinar::create($base + ['title' => 'Scheduled Attendance History', 'slug' => 'scheduled-history-'.uniqid(), 'status' => 'scheduled']);

        foreach ([$live, $scheduled] as $webinar) {
            DB::table('webinar_attendees')->insert([
                'webinar_id' => $webinar->id, 'user_id' => $learner->id,
                'joined_at' => '2026-09-13 05:50:00', 'watch_seconds' => 30,
                'last_seen_at' => '2026-09-13 05:50:30', 'raised_hand' => false,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $this->actingAs($admin)->get(route('admin.attendance'))
            ->assertOk()
            ->assertSee('Visible Live Webinar')
            ->assertSee('13 Sep, 11:20 AM IST')
            ->assertSee('0.42%')
            ->assertSee('Scheduled Attendance History');
    }

    public function test_admin_webinars_filters(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)
            ->get('/admin/webinars?search=Healthcare&status=scheduled')
            ->assertOk()
            ->assertSee('Search webinars by title')
            ->assertSee('All statuses')
            ->assertSee('Filter');
    }

    public function test_admin_headings_use_sidebar_names_without_workspace_copy(): void
    {
        $admin = $this->getSuperAdmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<h1>Dashboard</h1>', false)
            ->assertDontSee('ADMIN WORKSPACE')
            ->assertDontSee('GLOBAL OVERVIEW');

        $this->get(route('admin.webinars.index'))
            ->assertOk()
            ->assertSee('<h1>Webinars</h1>', false)
            ->assertSee('Add Webinar')
            ->assertDontSee('ADMIN WORKSPACE');

        $this->get(route('admin.webinars.create'))
            ->assertOk()
            ->assertSee('<h1>Add Webinar</h1>', false)
            ->assertDontSee('ADMIN WORKSPACE');

        $this->get(route('admin.polls.logs'))
            ->assertOk()
            ->assertSee('<h1>Poll Logs</h1>', false)
            ->assertDontSee('POLL AUDIT');
    }

    public function test_created_banner_and_brand_appear_on_frontend(): void
    {
        $webinar = Webinar::first();
        if (! $webinar) {
            $admin = $this->getSuperAdmin();
            $webinar = Webinar::create([
                'title' => 'Test Event 2026',
                'slug' => 'test-event-2026',
                'status' => 'scheduled',
                'created_by' => $admin->id,
            ]);
        }

        $brand = Brand::create([
            'webinar_id' => $webinar->id,
            'name' => 'Acme Alpha Partner',
            'logo_path' => 'https://placehold.co/200x80/2563EB/FFFFFF?text=ACME',
            'website_url' => 'https://acme.example.com',
            'is_active' => true,
        ]);

        $banner = Banner::create([
            'webinar_id' => $webinar->id,
            'title' => 'Spotlight Keynote Banner',
            'media_type' => 'image',
            'media_url' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1600&q=85',
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
            'display_order' => 1,
        ]);

        foreach (range(2, 9) as $brandNumber) {
            Brand::create([
                'webinar_id' => $webinar->id,
                'name' => 'Partner Brand '.$brandNumber,
                'logo_path' => 'https://placehold.co/200x80?text=BRAND'.$brandNumber,
                'is_active' => true,
            ]);
        }

        $visibleBrandCount = Brand::where('webinar_id', $webinar->id)->where('is_active', true)->count();

        $this->get('/'.$webinar->slug)
            ->assertOk()
            ->assertSee('Acme Alpha Partner')
            ->assertSee('data-brand-grid-toggle', false)
            ->assertSee('Show all '.$visibleBrandCount.' brands')
            ->assertSee('data-brand-card', false)
            ->assertSee('Spotlight Keynote Banner');
    }
}
