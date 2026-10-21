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
            
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            
            $table->decimal('amount_paid', 8, 2);
            $table->date('payment_date');
            $table->string('payment_method')->default('cash');
            $table->string('receipt_number')->unique();
            $table->text('notes')->nullable();
            
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
