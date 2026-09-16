<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        foreach (['poll-logs' => 'Poll Logs View', 'certificate-logs' => 'Certificate Logs View'] as $module => $name) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => $module.'.view'],
                ['name' => $name, 'module' => $module, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', ['poll-logs.view', 'certificate-logs.view'])->delete();
    }
};
