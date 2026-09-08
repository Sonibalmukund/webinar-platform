<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->text('message'); $table->foreignId('reply_to_id')->nullable()->constrained('chat_messages')->nullOnDelete(); $table->boolean('is_pinned')->default(false); $table->boolean('is_moderated')->default(false); $table->timestamp('sent_at')->useCurrent()->index(); $table->softDeletes(); $table->timestamps(); });
        Schema::create('questions', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->text('question'); $table->string('status', 30)->default('open')->index(); $table->boolean('is_anonymous')->default(false); $table->boolean('is_pinned')->default(false); $table->timestamp('answered_at')->nullable(); $table->timestamps(); });
        Schema::create('question_answers', function (Blueprint $table) { $table->id(); $table->foreignId('question_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->text('answer'); $table->boolean('is_official')->default(false); $table->timestamps(); });
        Schema::create('question_votes', function (Blueprint $table) { $table->id(); $table->foreignId('question_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->unique(['question_id','user_id']); $table->timestamps(); });
        Schema::create('polls', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); $table->text('question'); $table->boolean('allow_multiple')->default(false); $table->string('status', 30)->default('draft')->index(); $table->timestamp('started_at')->nullable(); $table->timestamp('ended_at')->nullable(); $table->timestamps(); });
        Schema::create('poll_options', function (Blueprint $table) { $table->id(); $table->foreignId('poll_id')->constrained()->cascadeOnDelete(); $table->string('label'); $table->unsignedSmallInteger('display_order')->default(0); $table->timestamps(); });
        Schema::create('poll_responses', function (Blueprint $table) { $table->id(); $table->foreignId('poll_id')->constrained()->cascadeOnDelete(); $table->foreignId('poll_option_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->unique(['poll_option_id','user_id']); $table->timestamps(); });
        Schema::create('webinar_reactions', function (Blueprint $table) { $table->id(); $table->foreignId('webinar_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('reaction', 30); $table->timestamp('reacted_at')->useCurrent()->index(); });
    }
    public function down(): void { foreach (['webinar_reactions','poll_responses','poll_options','polls','question_votes','question_answers','questions','chat_messages'] as $table) Schema::dropIfExists($table); }
};
