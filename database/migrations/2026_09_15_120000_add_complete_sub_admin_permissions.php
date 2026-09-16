<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $definitions = [
            'dashboard' => ['view'],
            'webinars' => ['view', 'create', 'edit', 'delete'],
            'dynamic-fields' => ['view', 'create', 'edit', 'delete'],
            'speakers' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'edit'],
            'registrations' => ['view', 'approve', 'export'],
            'attendance' => ['view', 'export'],
            'chat' => ['view', 'manage', 'moderate'],
            'q-and-a' => ['view', 'edit'],
            'polls' => ['view', 'create', 'edit', 'delete', 'manage'],
            'feedback' => ['view', 'edit'],
            'certificates' => ['view', 'create', 'edit', 'hide'],
            'notifications' => ['view', 'create'],
            'reports' => ['view', 'export'],
            'live-control' => ['view', 'manage'],
        ];
        $now = now();

        foreach ($definitions as $module => $actions) {
            foreach ($actions as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => "$module.$action"],
                    ['name' => ucwords(str_replace('-', ' ', $module)).' '.ucfirst($action), 'module' => $module, 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    public function down(): void
    {
        // Existing permission records may be in use, so rollback intentionally preserves them.
    }
};
