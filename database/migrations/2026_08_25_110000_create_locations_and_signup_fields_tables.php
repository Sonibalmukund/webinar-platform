<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->char('iso2', 2)->unique();
            $table->string('phone_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unique(['country_id', 'name']);
            $table->timestamps();
        });
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unique(['state_id', 'name']);
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('timezone')->constrained()->nullOnDelete();
            $table->foreignId('state_id')->nullable()->after('country_id')->constrained()->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('state_id')->constrained()->nullOnDelete();
        });
        Schema::create('signup_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('field_key')->unique();
            $table->enum('field_type', ['text', 'dropdown', 'radio', 'checkbox']);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });
        Schema::create('signup_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signup_field_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('signup_field_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signup_field_id')->constrained()->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->unique(['user_id', 'signup_field_id'], 'signup_answer_user_field_unique');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signup_field_answers');
        Schema::dropIfExists('signup_field_options');
        Schema::dropIfExists('signup_fields');
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('city_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('state_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('country_id'));
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
