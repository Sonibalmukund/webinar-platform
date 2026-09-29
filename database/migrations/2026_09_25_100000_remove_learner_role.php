<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notification_campaigns')) {
            DB::table('notification_campaigns')
                ->where('audience', 'all_learners')
                ->update(['audience' => 'all_registrations']);
        }

        if (! Schema::hasTable('roles')) {
            return;
        }

        $roleId = DB::table('roles')->where('slug', 'learner')->value('id');
        if (! $roleId) {
            return;
        }

        if (Schema::hasTable('role_user')) {
            DB::table('role_user')->where('role_id', $roleId)->delete();
        }
        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')->where('role_id', $roleId)->delete();
        }

        DB::table('roles')->where('id', $roleId)->delete();
    }

    public function down(): void
    {
        if (Schema::hasTable('roles') && ! DB::table('roles')->where('slug', 'learner')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Learner',
                'slug' => 'learner',
                'description' => 'Legacy webinar attendee access',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('notification_campaigns')) {
            DB::table('notification_campaigns')
                ->where('audience', 'all_registrations')
                ->update(['audience' => 'all_learners']);
        }
    }
};
