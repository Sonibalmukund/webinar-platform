<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('webinars')->where('qa_enabled', true)->update([
            'qa_enabled' => false,
            'comments_enabled' => true,
        ]);
    }

    public function down(): void
    {
        // Existing question data is preserved; this presentation change is intentionally not reversed.
    }
};
