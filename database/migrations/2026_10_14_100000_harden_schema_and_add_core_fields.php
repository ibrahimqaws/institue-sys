<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- users ----------
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 12, 2)->nullable()->after('is_active');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->change();
        });

        DB::table('users')->where('role', 'user')->update(['role' => 'student']);

        // ---------- batches ----------
        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'days')) {
                $table->string('days')->nullable();
            }
            if (! Schema::hasColumn('batches', 'start_time')) {
                $table->time('start_time')->nullable();
            }
            if (! Schema::hasColumn('batches', 'end_time')) {
                $table->time('end_time')->nullable();
            }
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->unsignedBigInteger('classroom_id')->nullable()->change();
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->change();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 12, 2)->change();
        });

        if (Schema::hasTable('batch_student')) {
            foreach (DB::table('batch_student')->get() as $row) {
                DB::table('enrollments')->insertOrIgnore([
                    'student_id' => $row->student_id,
                    'batch_id' => $row->batch_id,
                    'enrollment_date' => optional($row->created_at)
                        ? date('Y-m-d', strtotime($row->created_at))
                        : now()->toDateString(),
                    'status' => 'active',
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }

        // ---------- payrolls ----------
        if (! Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->date('period_month');
                $table->decimal('base_amount', 12, 2)->default(0);
                $table->decimal('bonus', 12, 2)->default(0);
                $table->decimal('deductions', 12, 2)->default(0);
                $table->decimal('net_amount', 12, 2)->default(0);
                $table->string('status')->default('pending');       // pending | paid
                $table->date('paid_at')->nullable();
                $table->foreignId('expense_id')->nullable()->constrained('expenses')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'period_month']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');

        Schema::table('batches', function (Blueprint $table) {
            foreach (['days', 'start_time', 'end_time'] as $col) {
                if (Schema::hasColumn('batches', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['is_active', 'phone', 'salary'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
