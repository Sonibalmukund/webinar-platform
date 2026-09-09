<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_fields', function (Blueprint $table) {
            $table->foreignId('condition_field_id')->nullable()->after('validation_rules')->constrained('registration_fields')->nullOnDelete();
            $table->string('condition_operator', 20)->nullable()->after('condition_field_id');
            $table->string('condition_value')->nullable()->after('condition_operator');
        });
        Schema::create('webinar_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webinar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('event_type', ['join', 'heartbeat', 'leave']);
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webinar_attendance_events');
        Schema::table('registration_fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('condition_field_id');
            $table->dropColumn(['condition_operator', 'condition_value']);
        });
    }
};
