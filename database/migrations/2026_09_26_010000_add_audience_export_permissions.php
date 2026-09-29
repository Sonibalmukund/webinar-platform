<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $permissions = [
        'users.export' => ['Users Export', 'users'],
        'poll-logs.export' => ['Poll Logs Export', 'poll-logs'],
        'q-and-a.export' => ['Q And A Export', 'q-and-a'],
        'feedback.export' => ['Feedback Export', 'feedback'],
    ];

    public function up(): void
    {
        $now = now();
        $superAdminRoleId = DB::table('roles')->where('slug', 'super-admin')->value('id');

        foreach ($this->permissions as $slug => [$name, $module]) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $slug],
                ['name' => $name, 'module' => $module, 'created_at' => $now, 'updated_at' => $now]
            );

            if ($superAdminRoleId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => DB::table('permissions')->where('slug', $slug)->value('id'),
                    'role_id' => $superAdminRoleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', array_keys($this->permissions))->delete();
    }
};
