<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (!Schema::hasColumn('batches', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('batches', 'subject_name')) {
                $table->string('subject_name')->nullable();
            }
            if (!Schema::hasColumn('batches', 'schedule_time')) {
                $table->string('schedule_time')->nullable();
            }
            if (!Schema::hasColumn('batches', 'room_number')) {
                $table->string('room_number')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn(['teacher_id', 'subject_name', 'schedule_time', 'room_number']);
        });
    }
};