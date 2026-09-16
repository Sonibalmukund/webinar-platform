<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestingWebinarSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        Role::firstOrCreate(
            ['slug' => 'learner'],
            ['name' => 'Learner', 'description' => 'Webinar attendee access', 'is_system' => true]
        );

        $india = DB::table('countries')->where('iso2', 'IN')->first();
        $gujarat = $india ? DB::table('states')->where('country_id', $india->id)->where('name', 'Gujarat')->first() : null;
        foreach ([
            ['group' => 'registration', 'key' => 'registration_email_enabled', 'value' => '1', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_email_required', 'value' => '1', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_mobile_enabled', 'value' => '1', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_mobile_required', 'value' => '0', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_password_enabled', 'value' => '0', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_password_required', 'value' => '0', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_country_enabled', 'value' => '1', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_state_enabled', 'value' => '1', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_city_enabled', 'value' => '1', 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_default_country_id', 'value' => (string) ($india->id ?? ''), 'is_public' => false],
            ['group' => 'registration', 'key' => 'registration_default_state_id', 'value' => (string) ($gujarat->id ?? ''), 'is_public' => false],
            ['group' => 'site', 'key' => 'site_name', 'value' => 'Virtual Portal', 'is_public' => true],
            ['group' => 'site', 'key' => 'footer_text', 'value' => '© 2026 Webinar Testing Portal. All rights reserved.', 'is_public' => true],
        ] as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $startsAt = now()->subMinutes(5)->startOfMinute();
        $endsAt = now()->addDay()->startOfMinute();
        $webinar = Webinar::create([
            'created_by' => $admin->id,
            'title' => 'Registration & Attendance Testing Webinar',
            'slug' => 'registration-attendance-testing',
            'short_description' => 'Fresh webinar created for registration, room entry, and attendance testing.',
            'description' => 'Register with a new attendee account, enter the live room, and verify attendance from the admin panel.',
            'status' => 'live',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'max_attendees' => 100,
            'registration_type' => 'free',
            'published_at' => now(),
            'registration_deadline' => $endsAt,
            'live_provider' => null,
            'live_url' => null,
            'certificate_enabled' => 'yes',
            'chat_enabled' => true,
            'qa_enabled' => true,
            'polls_enabled' => false,
            'comments_enabled' => true,
            'feedback_enabled' => true,
            'auto_approve' => true,
            'early_entry_minutes' => 30,
            'settings' => [
                'experience' => [
                    'primary' => '#ee1f2d',
                    'secondary' => '#991b1b',
                    'layout' => 'presentation',
                    'logo_url' => '/images/testing-client-logo.svg',
                    'waiting_media_url' => '/images/default-banner.jpg',
                    'waiting_message' => 'The testing webinar is ready. Register and enter the room.',
                    'post_message' => 'Attendance testing is complete. Thank you.',
                    'registration_success_title' => 'Registration successful!',
                    'registration_success_message' => 'Your test seat is confirmed. Enter the room to start attendance tracking.',
                    'certificate_min_attendance' => 10,
                    'certificate_require_poll' => false,
                ],
            ],
        ]);

        Banner::create([
            'webinar_id' => $webinar->id,
            'title' => 'Testing Webinar Banner',
            'media_type' => 'image',
            'media_path' => '/images/default-banner.jpg',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $form = $webinar->registrationForm()->create([
            'title' => 'Register for the testing webinar',
            'description' => 'Create your attendee account and test registration and attendance.',
            'is_active' => true,
            'require_login' => true,
            'success_message' => 'Registration completed successfully.',
        ]);

        foreach ([
            ['label' => 'Full Name', 'field_key' => 'full_name', 'field_type' => 'text', 'placeholder' => 'Enter your full name', 'is_required' => true, 'login_enabled' => false, 'width' => 'half'],
            ['label' => 'Email Address', 'field_key' => 'email', 'field_type' => 'text', 'placeholder' => 'you@example.com', 'is_required' => true, 'login_enabled' => true, 'width' => 'half'],
            ['label' => 'Mobile Number', 'field_key' => 'mobile', 'field_type' => 'text', 'placeholder' => '+91 98765 43210', 'is_required' => false, 'login_enabled' => false, 'width' => 'half'],
            ['label' => 'City', 'field_key' => 'city', 'field_type' => 'city', 'placeholder' => 'Select city', 'is_required' => true, 'login_enabled' => false, 'width' => 'half'],
        ] as $order => $field) {
            $form->fields()->create($field + [
                'icon' => 'input-cursor-text',
                'is_enabled' => true,
                'display_order' => $order + 1,
            ]);
        }
    }
}
