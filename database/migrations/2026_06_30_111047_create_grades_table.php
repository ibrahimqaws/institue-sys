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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            
            $table->string('exam_name');
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->decimal('student_score', 5, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
