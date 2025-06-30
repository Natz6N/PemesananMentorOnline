<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mentor_profile_id')->constrained()->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->decimal('total_amount', 10, 2);
            $table->text('session_topic');
            $table->text('student_notes')->nullable();
            $table->text('mentor_notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->string('meeting_link')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
