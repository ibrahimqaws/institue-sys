<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'date')) {
                $table->date('date')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('cascade');
            }
            if (!Schema::hasColumn('attendances', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('attendances', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('attendances', 'status')) {
                $table->string('status')->default('present');
            }

            if (Schema::hasColumn('attendances', 'attendance_date')) {
                $table->date('attendance_date')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['date', 'batch_id', 'student_id', 'teacher_id', 'status']);
        });
    }
};