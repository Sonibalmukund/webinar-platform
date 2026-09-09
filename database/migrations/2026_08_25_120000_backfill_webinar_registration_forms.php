<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('webinars')->whereNotExists(function ($query) {
            $query->selectRaw('1')->from('registration_forms')->whereColumn('registration_forms.webinar_id', 'webinars.id');
        })->orderBy('id')->each(function ($webinar) use ($now) {
            DB::table('registration_forms')->insert(['webinar_id' => $webinar->id, 'title' => 'Registration Form', 'is_active' => true, 'require_login' => true, 'created_at' => $now, 'updated_at' => $now]);
        });
    }

    public function down(): void {}
};
