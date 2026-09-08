<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webinar_agenda_items', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->text('description')->nullable(); $table->time('starts_at')->nullable(); $table->unsignedSmallInteger('duration_minutes')->nullable(); $table->unsignedSmallInteger('display_order')->default(0); $table->timestamps(); });
        Schema::create('webinar_resources', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete(); $table->string('title'); $table->string('type', 30)->default('file'); $table->text('path_or_url'); $table->boolean('is_public')->default(false); $table->unsignedSmallInteger('display_order')->default(0); $table->timestamps(); });
        Schema::create('webinar_attendees', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->timestamp('joined_at')->nullable(); $table->timestamp('left_at')->nullable(); $table->unsignedInteger('watch_seconds')->default(0); $table->timestamp('last_seen_at')->nullable(); $table->boolean('raised_hand')->default(false); $table->json('metadata')->nullable(); $table->unique(['webinar_id','user_id']); $table->timestamps(); });
        Schema::create('webinar_bookmarks', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->unique(['webinar_id','user_id']); $table->timestamps(); });
        Schema::create('webinar_recordings', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->string('title'); $table->text('recording_url'); $table->string('thumbnail_path')->nullable(); $table->unsignedInteger('duration_seconds')->nullable(); $table->string('status', 30)->default('processing')->index(); $table->timestamp('published_at')->nullable(); $table->timestamps(); });
        Schema::create('webinar_reviews', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->unsignedTinyInteger('rating'); $table->text('review')->nullable(); $table->boolean('is_approved')->default(false); $table->unique(['webinar_id','user_id']); $table->timestamps(); });
        Schema::create('feedback', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->nullable()->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->unsignedTinyInteger('rating')->nullable(); $table->text('message'); $table->string('status', 30)->default('new')->index(); $table->timestamps(); });
    }
    public function down(): void { foreach (['feedback','webinar_reviews','webinar_recordings','webinar_bookmarks','webinar_attendees','webinar_resources','webinar_agenda_items'] as $table) Schema::dropIfExists($table); }
};
