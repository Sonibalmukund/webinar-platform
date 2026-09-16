<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_responses', function (Blueprint $table) {
            $table->index('poll_id', 'poll_responses_poll_id_index');
            $table->dropUnique('poll_response_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('poll_responses', function (Blueprint $table) {
            $table->unique(['poll_id', 'user_id'], 'poll_response_user_unique');
            $table->dropIndex('poll_responses_poll_id_index');
        });
    }
};
