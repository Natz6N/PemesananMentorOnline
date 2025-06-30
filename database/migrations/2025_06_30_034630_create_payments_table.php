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
         Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('payment_code')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // credit_card, bank_transfer, ewallet, etc
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('external_id')->nullable(); // Payment gateway transaction ID
            $table->json('payment_details')->nullable(); // Store payment gateway response
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
