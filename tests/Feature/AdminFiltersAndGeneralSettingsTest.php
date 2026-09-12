<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
            ->assertRedirect('/admin/users?status=approved');
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

    public function test_created_banner_and_brand_appear_on_frontend(): void
    {
        $webinar = Webinar::first();
        if (!$webinar) {
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

        $this->get('/' . $webinar->slug)
            ->assertOk()
            ->assertSee('Acme Alpha Partner')
            ->assertSee('Spotlight Keynote Banner');
    }
}
