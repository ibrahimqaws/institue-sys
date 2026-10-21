<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (!Schema::hasColumn('grades', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('grades', 'score')) {
                $table->decimal('score', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('grades', 'max_score')) {
                $table->decimal('max_score', 5, 2)->default(100);
            }
            if (!Schema::hasColumn('grades', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('cascade');
            }
            if (!Schema::hasColumn('grades', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['title', 'score', 'max_score', 'batch_id', 'student_id']);
        });
    }
};