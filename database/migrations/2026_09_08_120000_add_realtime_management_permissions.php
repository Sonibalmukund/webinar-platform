<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $definitions = [
        'chat' => ['view', 'manage', 'moderate'],
        'attendance' => ['view', 'export'],
        'notifications' => ['view', 'create'],
    ];

    public function up(): void
    {
        $now = now();
        $superRoleId = DB::table('roles')->where('slug', 'super-admin')->value('id');
        foreach ($this->definitions as $module => $actions) {
            foreach ($actions as $action) {
                $slug = $module.'.'.$action;
                DB::table('permissions')->updateOrInsert(['slug' => $slug], ['name' => ucwords($module).' '.ucfirst($action), 'module' => $module, 'updated_at' => $now, 'created_at' => $now]);
                if ($superRoleId) {
                    DB::table('permission_role')->insertOrIgnore(['permission_id' => DB::table('permissions')->where('slug', $slug)->value('id'), 'role_id' => $superRoleId]);
                }
            }
        }
    }

    public function down(): void
    {
        $slugs = [];
        foreach ($this->definitions as $module => $actions) {
            foreach ($actions as $action) {
                $slugs[] = $module.'.'.$action;
            }
        }
        DB::table('permissions')->whereIn('slug', $slugs)->delete();
    }
};
