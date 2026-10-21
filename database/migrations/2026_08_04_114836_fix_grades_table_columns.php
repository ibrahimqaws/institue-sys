<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('exam_name')->nullable()->change();
            $table->decimal('student_score', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('exam_name')->nullable(false)->change();
            $table->decimal('student_score', 5, 2)->nullable(false)->change();
        });
    }
};