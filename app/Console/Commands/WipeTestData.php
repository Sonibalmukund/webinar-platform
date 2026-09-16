<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WipeTestData extends Command
{
    protected $signature = 'app:wipe-test-data {--force : Force deletion without prompt}';

    protected $description = 'Wipe all test webinars, attendees, registrations and non-admin data while keeping Super Admin account';

    public function handle(): int
    {
        $superAdmin = User::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->first();

        if (! $superAdmin) {
            $this->error('No super-admin user found! Aborting for safety.');
            return self::FAILURE;
        }

        $this->warn("Super Admin to preserve: ID {$superAdmin->id} ({$superAdmin->name} - {$superAdmin->email})");

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to wipe all test data and non-admin users?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();

        try {
            DB::beginTransaction();

            // 1. Wipe webinar activity & attendee data
            DB::table('webinar_attendance_events')->delete();
            DB::table('webinar_attendees')->delete();
            DB::table('webinar_bookmarks')->delete();
            DB::table('webinar_reactions')->delete();
            DB::table('webinar_recordings')->delete();
            DB::table('webinar_reviews')->delete();
            DB::table('webinar_resources')->delete();
            DB::table('webinar_agenda_items')->delete();

            // 2. Wipe registrations & custom answers
            DB::table('registration_answers')->delete();
            DB::table('registrations')->delete();
            DB::table('signup_field_answers')->delete();

            // 3. Wipe chat & Q&A
            DB::table('chat_message_votes')->delete();
            DB::table('chat_messages')->delete();
            DB::table('question_votes')->delete();
            DB::table('question_answers')->delete();
            DB::table('questions')->delete();
            DB::table('comments')->delete();

            // 4. Wipe feedback & certificates
            DB::table('feedback')->delete();
            DB::table('certificate_downloads')->delete();
            DB::table('certificates')->delete();

            // 5. Wipe polls
            DB::table('poll_responses')->delete();
            DB::table('poll_options')->delete();
            DB::table('polls')->delete();

            // 6. Wipe notifications & assignments
            DB::table('user_notifications')->delete();
            DB::table('user_webinar_permissions')->delete();
            DB::table('user_webinar_assignments')->delete();
            DB::table('notification_campaigns')->delete();

            // 7. Wipe speakers, banners, brands (test media) & associations
            DB::table('speaker_webinar')->delete();
            DB::table('speakers')->delete();
            DB::table('banners')->delete();
            DB::table('brands')->delete();

            // 8. Wipe category associations & webinars
            DB::table('category_webinar')->delete();
            DB::table('webinars')->delete();

            // 9. Wipe non-admin users and their role assignments
            $nonAdminUserIds = User::where('id', '!=', $superAdmin->id)->pluck('id');
            DB::table('role_user')->whereIn('user_id', $nonAdminUserIds)->delete();
            User::where('id', '!=', $superAdmin->id)->delete();

            DB::commit();

            $this->info("Wipe complete! Super Admin ({$superAdmin->email}) preserved. Database is completely clean for fresh testing.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to wipe data: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
