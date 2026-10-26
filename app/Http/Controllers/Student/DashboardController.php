<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Payment;
use App\Models\StudentNote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const METHOD_LABELS = ['cash' => 'نقدي', 'transfer' => 'تحويل', 'card' => 'بطاقة'];

    private const NOTE_LABELS = ['academic' => 'أكاديمية', 'behavioral' => 'سلوكية'];

    public function index(Request $request): Response
    {
        $studentId = $request->user()->id;

        $enrollments = Enrollment::where('student_id', $studentId)
            ->with(['batch.course:id,title,price,duration_hours', 'batch.classroom:id,name', 'batch.teacher:id,name'])
            ->latest('id')
            ->get();

        $active = $enrollments->where('status', Enrollment::STATUS_ACTIVE);
        $accessible = $enrollments->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED]);

        $attendance = $this->attendance($studentId);
        $finance = $this->finance($studentId, $accessible);

        return Inertia::render('Student/Dashboard', [
            'stats' => [
                'attendance_rate' => $attendance['summary']['rate'],
                'active_courses' => $active->count(),
                'pending_requests' => $enrollments->where('status', Enrollment::STATUS_PENDING)->count(),
                'remaining' => $finance['totals']['remaining'],
            ],
            'enrollments' => $enrollments->map(fn (Enrollment $e) => [
                'id' => $e->id,
                'status' => $e->status,
                'enrollment_date' => optional($e->enrollment_date)->format('Y-m-d'),
                'batch' => $this->batchPayload($e->batch),
            ])->values(),
            'schedule' => $active
                ->filter(fn (Enrollment $e) => $e->batch && $e->batch->status === 'active')
                ->map(fn (Enrollment $e) => $this->batchPayload($e->batch))
                ->values(),
            'dayNames' => Batch::DAYS,
            'attendance' => $attendance['records'],
            'attendanceSummary' => $attendance['summary'],
            'attendanceByBatch' => $attendance['by_batch'],
            'grades' => $this->grades($studentId),
            'notes' => $this->notes($studentId),
            'materials' => $this->materials($accessible->pluck('batch_id')),
            'finance' => $finance,
        ]);
    }

    private function batchPayload(?Batch $b): ?array
    {
        if (! $b) {
            return null;
        }

        return [
            'id' => $b->id,
            'name' => $b->name,
            'status' => $b->status,
            'course_title' => $b->course?->title,
            'teacher' => $b->teacher?->name,
            'room' => $b->classroom?->name ?? $b->room_number,
            'days' => array_values(array_filter(array_map('trim', explode(',', (string) $b->days)))),
            'start_time' => $b->start_time ? substr($b->start_time, 0, 5) : null,
            'end_time' => $b->end_time ? substr($b->end_time, 0, 5) : null,
            'schedule_label' => $b->schedule_label,
            'start_date' => optional($b->start_date)->format('Y-m-d'),
            'end_date' => optional($b->end_date)->format('Y-m-d'),
        ];
    }

    
    private function attendance(int $studentId): array
    {
        $rows = Attendance::where('student_id', $studentId)
            ->with(['batch:id,name,course_id', 'batch.course:id,title'])
            ->orderByRaw('COALESCE(attendance_date, date) DESC')
            ->orderByDesc('id')
            ->get();

        $records = $rows->map(fn (Attendance $a) => [
            'id' => $a->id,
            'batch_id' => $a->batch_id,
            'course_title' => $a->batch?->course?->title,
            'batch_name' => $a->batch?->name,
            'date' => optional($a->attendance_date ?? $a->date)->format('Y-m-d'),
            'status' => $a->status,
            'remarks' => $a->remarks,
        ])->values();

        $summarize = function ($group): array {
            $total = $group->count();
            $present = $group->where('status', 'present')->count();
            $late = $group->where('status', 'late')->count();
            $absent = $group->where('status', 'absent')->count();

            return [
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'rate' => $total ? round(($present + $late) / $total * 100, 1) : null,
            ];
        };

        return [
            'records' => $records,
            'summary' => $summarize($rows),
            'by_batch' => $rows->groupBy('batch_id')->map(function ($group, $batchId) use ($summarize) {
                $first = $group->first();

                return ['batch_id' => $batchId]
                    + ['course_title' => $first->batch?->course?->title, 'batch_name' => $first->batch?->name]
                    + $summarize($group);
            })->values(),
        ];
    }

    
    private function grades(int $studentId)
    {
        return Grade::where('student_id', $studentId)
            ->with(['batch:id,name,course_id', 'batch.course:id,title'])
            ->latest('id')
            ->get()
            ->map(fn (Grade $g) => [
                'id' => $g->id,
                'batch_id' => $g->batch_id,
                'course_title' => $g->batch?->course?->title,
                'batch_name' => $g->batch?->name,
                'title' => $g->title ?? $g->exam_name,
                'score' => $g->score ?? $g->student_score,
                'max_score' => $g->max_score,
                'percentage' => $g->percentage,
                'date' => optional($g->created_at)->format('Y-m-d'),
            ])->values();
    }

    
    private function notes(int $studentId)
    {
        return StudentNote::where('student_id', $studentId)
            ->with(['teacher:id,name', 'batch:id,name,course_id', 'batch.course:id,title'])
            ->latest('id')
            ->get()
            ->map(fn (StudentNote $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'type_label' => self::NOTE_LABELS[$n->type] ?? $n->type,
                'content' => $n->content,
                'teacher' => $n->teacher?->name,
                'course_title' => $n->batch?->course?->title,
                'batch_name' => $n->batch?->name,
                'date' => optional($n->created_at)->format('Y-m-d'),
            ])->values();
    }

    
    private function materials($batchIds)
    {
        return Material::whereIn('batch_id', $batchIds)
            ->with(['batch:id,name,course_id', 'batch.course:id,title', 'teacher:id,name'])
            ->latest('id')
            ->get()
            ->map(fn (Material $m) => [
                'id' => $m->id,
                'title' => $m->title,
                'description' => $m->description,
                'type' => $m->type,
                'course_title' => $m->batch?->course?->title,
                'batch_name' => $m->batch?->name,
                'teacher' => $m->teacher?->name,
                'due_date' => optional($m->due_date)->format('Y-m-d H:i'),
                'is_overdue' => $m->type === 'assignment' && $m->due_date && $m->due_date->isPast(),
                'has_file' => (bool) $m->file_path,
                'download_url' => $m->file_path ? route('student.materials.download', $m) : null,
                'created_at' => optional($m->created_at)->format('Y-m-d'),
            ])->values();
    }

    
    private function finance(int $studentId, $accessibleEnrollments): array
    {
        $courses = $accessibleEnrollments->map(fn (Enrollment $e) => [
            'enrollment_id' => $e->id,
            'course_title' => $e->batch?->course?->title,
            'batch_name' => $e->batch?->name,
            'status' => $e->status,
            'fee' => $e->totalFee(),
            'paid' => $e->totalPaid(),
            'remaining' => $e->remaining(),
        ])->values();

        $payments = Payment::where('student_id', $studentId)
            ->with(['batch:id,name,course_id', 'batch.course:id,title'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'receipt_number' => $p->receipt_number,
                'course_title' => $p->batch?->course?->title,
                'batch_name' => $p->batch?->name,
                'amount' => (float) $p->amount_paid,
                'payment_date' => optional($p->payment_date)->format('Y-m-d'),
                'method_label' => self::METHOD_LABELS[$p->payment_method] ?? $p->payment_method,
            ])->values();

        return [
            'courses' => $courses,
            'payments' => $payments,
            'totals' => [
                'fee' => round($courses->sum('fee'), 2),
                'paid' => round($courses->sum('paid'), 2),
                'remaining' => round($courses->sum('remaining'), 2),
            ],
        ];
    }
}
