<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio');
            $table->json('expertise'); // Array of skills/expertise
            $table->integer('experience_years');
            $table->string('education')->nullable();
            $table->string('current_position')->nullable();
            $table->string('company')->nullable();
            $table->text('achievements')->nullable();
            $table->decimal('hourly_rate', 10, 2);
            $table->string('timezone')->default('Asia/Jakarta');
            $table->json('languages')->nullable(); // Array of languages
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->integer('total_sessions')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_profiles');
    }
};
