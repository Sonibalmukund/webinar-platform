<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Database\Seeder;

class DemoLayoutWebinarsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $learnerRole = Role::firstOrCreate(
            ['slug' => 'learner'],
            ['name' => 'Learner', 'description' => 'Webinar attendee access', 'is_system' => true]
        );
        $learner = User::firstOrCreate(
            ['email' => 'layout.tester@webinar.test'],
            ['name' => 'Layout Test Attendee', 'mobile' => '+91 90000 00002', 'password' => str()->random(40), 'status' => 'active', 'timezone' => 'Asia/Kolkata']
        );
        $learner->roles()->syncWithoutDetaching([$learnerRole->id]);

        $examples = [
            [
                'title' => 'Theater Mode Test Webinar',
                'slug' => 'theater-mode-test-webinar',
                'layout' => 'theater',
                'starts_at' => now()->addMinutes(15)->seconds(0),
                'early_entry_minutes' => 30,
                'description' => 'A test event for the full-screen theater iframe followed by the interactive webinar content.',
            ],
            [
                'title' => 'Interactive Mode Test Webinar',
                'slug' => 'interactive-mode-test-webinar',
                'layout' => 'presentation',
                'starts_at' => now()->addMinutes(30)->seconds(0),
                'early_entry_minutes' => 30,
                'description' => 'A test event for the stream-and-sidebar interactive room layout.',
            ],
        ];

        foreach ($examples as $example) {
            $startsAt = $example['starts_at'];
            $webinar = Webinar::updateOrCreate(
                ['slug' => $example['slug']],
                [
                    'created_by' => $admin->id,
                    'title' => $example['title'],
                    'short_description' => $example['description'],
                    'description' => $example['description'],
                    'status' => 'scheduled',
                    'language' => 'en',
                    'timezone' => 'Asia/Kolkata',
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addMinutes(60),
                    'max_attendees' => 100,
                    'registration_type' => 'free',
                    'published_at' => now(),
                    'registration_deadline' => $startsAt->copy()->addMinutes(10),
                    'live_provider' => 'youtube',
                    'live_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
                    'certificate_enabled' => 'yes',
                    'chat_enabled' => true,
                    'qa_enabled' => true,
                    'polls_enabled' => true,
                    'comments_enabled' => true,
                    'feedback_enabled' => true,
                    'auto_approve' => true,
                    'early_entry_minutes' => $example['early_entry_minutes'],
                    'settings' => ['experience' => [
                        'layout' => $example['layout'],
                        'primary' => '#6d28d9',
                        'secondary' => '#2563eb',
                        'waiting_message' => 'Early access is open. Attendance starts at the scheduled webinar time.',
                    ]],
                ]
            );

            $form = $webinar->registrationForm()->updateOrCreate([], [
                'title' => 'Register for '.$example['title'],
                'description' => 'Passwordless test registration.',
                'is_active' => true,
                'require_login' => true,
                'success_message' => 'Registration confirmed.',
            ]);
            foreach ([
                ['field_key' => 'full_name', 'label' => 'Full name', 'placeholder' => 'Enter your full name', 'login_enabled' => false],
                ['field_key' => 'email', 'label' => 'Email address', 'placeholder' => 'you@example.com', 'login_enabled' => true],
                ['field_key' => 'organization', 'label' => 'Organization', 'placeholder' => 'Company or institution', 'login_enabled' => false],
            ] as $order => $field) {
                $form->fields()->updateOrCreate(['field_key' => $field['field_key']], $field + [
                    'field_type' => 'text', 'is_required' => $field['field_key'] !== 'organization',
                    'is_enabled' => true, 'display_order' => $order + 1,
                ]);
            }

            $poll = Poll::updateOrCreate(
                ['webinar_id' => $webinar->id, 'question' => 'Which room mode is being tested?'],
                ['created_by' => $admin->id, 'allow_multiple' => false, 'status' => 'active', 'started_at' => now()]
            );
            if (! $poll->options()->exists()) {
                foreach (['Theater mode', 'Interactive mode', 'Audio only'] as $index => $label) {
                    $poll->options()->create([
                        'label' => $label,
                        'is_correct' => $label === ($example['layout'] === 'theater' ? 'Theater mode' : 'Interactive mode'),
                        'display_order' => $index,
                    ]);
                }
            }

            $webinar->registrations()->updateOrCreate(
                ['user_id' => $learner->id],
                ['email' => $learner->email, 'status' => 'approved', 'source' => 'layout-demo-seeder', 'registered_at' => now(), 'approved_at' => now()]
            );
        }
    }
}
