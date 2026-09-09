<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_options', function (Blueprint $table) {
            $table->boolean('is_correct')->default(false)->after('label')->index();
        });
        Schema::table('poll_responses', function (Blueprint $table) {
            $table->boolean('is_correct')->nullable()->after('user_id');
            $table->timestamp('voted_at')->nullable()->after('is_correct');
            $table->unique(['poll_id', 'user_id'], 'poll_response_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('poll_responses', function (Blueprint $table) {
            $table->dropUnique('poll_response_user_unique');
            $table->dropColumn(['is_correct', 'voted_at']);
        });
        Schema::table('poll_options', fn (Blueprint $table) => $table->dropColumn('is_correct'));
    }
};
