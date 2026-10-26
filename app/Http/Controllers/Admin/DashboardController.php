<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $monthStart = now()->startOfMonth()->toDateString();

        $rows = Attendance::select('batch_id', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('batch_id', 'status')->get();

        $overall = ['present' => 0, 'absent' => 0, 'late' => 0];
        $perBatch = [];
        foreach ($rows as $r) {
            if (isset($overall[$r->status])) {
                $overall[$r->status] += (int) $r->total;
            }
            $perBatch[$r->batch_id][$r->status] = (int) $r->total;
        }

        $names = Batch::whereIn('id', array_keys($perBatch))->with('course:id,title')->get()->keyBy('id');

        $attendanceByBatch = collect($perBatch)->map(function ($c, $batchId) use ($names) {
            $total = array_sum($c);
            $absent = $c['absent'] ?? 0;

            return [
                'batch' => $names[$batchId]->name ?? "#{$batchId}",
                'course' => $names[$batchId]->course->title ?? '',
                'records' => $total,
                'absent' => $absent,
                'late' => $c['late'] ?? 0,
                'absence_rate' => $total ? round($absent / $total * 100, 1) : 0,
            ];
        })->sortByDesc('absence_rate')->values();

        $totalRecords = array_sum($overall);

        $paid = Payment::select('student_id', 'batch_id', DB::raw('SUM(amount_paid) as paid'))
            ->groupBy('student_id', 'batch_id')->get()
            ->mapWithKeys(fn ($p) => [$p->student_id . '-' . $p->batch_id => (float) $p->paid]);

        $outstanding = Enrollment::with('batch.course:id,price')->where('status', 'active')->get()
            ->sum(fn ($e) => max(0, (float) ($e->batch?->course?->price ?? 0) - ($paid[$e->student_id . '-' . $e->batch_id] ?? 0)));

        $revenueMonth = (float) Payment::whereDate('payment_date', '>=', $monthStart)->sum('amount_paid');
        $expensesMonth = (float) Expense::whereDate('expense_date', '>=', $monthStart)->sum('amount');

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'students' => User::where('role', 'student')->where('is_active', true)->count(),
                'teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
                'courses' => Course::count(),
                'active_batches' => Batch::where('status', 'active')->count(),
                'pending_enrollments' => Enrollment::where('status', 'pending')->count(),
            ],
            'finance' => [
                'revenue_month' => $revenueMonth,
                'expenses_month' => $expensesMonth,
                'profit_month' => $revenueMonth - $expensesMonth,
                'outstanding' => round($outstanding, 2),
            ],
            'attendance' => [
                'records' => $totalRecords,
                'present' => $overall['present'],
                'absent' => $overall['absent'],
                'late' => $overall['late'],
                'absence_rate' => $totalRecords ? round($overall['absent'] / $totalRecords * 100, 1) : 0,
                'by_batch' => $attendanceByBatch,
            ],
        ]);
    }
}
