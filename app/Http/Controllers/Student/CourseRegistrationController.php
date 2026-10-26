<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Enrollment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseRegistrationController extends Controller
{
    public function availableCourses(Request $request): Response
    {
        $studentId = $request->user()->id;

        $batches = $this->openBatches()
            ->with(['course:id,title,description,price,duration_hours', 'classroom:id,name,capacity', 'teacher:id,name'])
            ->withCount(['enrollments as active_count' => fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)])
            ->orderBy('start_date')
            ->get();

        $mine = Enrollment::where('student_id', $studentId)
            ->whereIn('batch_id', $batches->pluck('id'))
            ->pluck('status', 'batch_id');

        return Inertia::render('Student/Courses/Register', [
            'batches' => $batches->map(function (Batch $b) use ($mine) {
                $capacity = $b->classroom?->capacity;

                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'course' => [
                        'title' => $b->course?->title,
                        'description' => $b->course?->description,
                        'price' => (float) ($b->course?->price ?? 0),
                        'duration_hours' => $b->course?->duration_hours,
                    ],
                    'teacher' => $b->teacher?->name,
                    'room' => $b->classroom?->name ?? $b->room_number,
                    'schedule_label' => $b->schedule_label,
                    'start_date' => optional($b->start_date)->format('Y-m-d'),
                    'end_date' => optional($b->end_date)->format('Y-m-d'),
                    'seats_left' => $capacity ? max(0, $capacity - $b->active_count) : null,
                    'my_status' => $mine[$b->id] ?? null,
                ];
            })->values(),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'batch_id' => 'required|integer|exists:batches,id',
        ]);

        $studentId = $request->user()->id;

        $batch = $this->openBatches()->with('classroom:id,name,capacity')->find($data['batch_id']);

        if (! $batch) {
            return back()->with('error', 'هذه المجموعة غير متاحة للتسجيل حالياً.');
        }

        $existing = Enrollment::where('student_id', $studentId)->where('batch_id', $batch->id)->first();

        if ($existing) {
            $message = match ($existing->status) {
                Enrollment::STATUS_PENDING => 'لديك طلب تسجيل قيد المراجعة في هذه المجموعة بالفعل.',
                Enrollment::STATUS_ACTIVE => 'أنت مسجّل في هذه المجموعة بالفعل.',
                Enrollment::STATUS_COMPLETED => 'لقد أنهيت هذه المجموعة سابقاً.',
                default => null,
            };

            if ($message) {
                return back()->with('error', $message);
            }
        }

        $capacity = $batch->classroom?->capacity;
        if ($capacity) {
            $active = Enrollment::where('batch_id', $batch->id)->where('status', Enrollment::STATUS_ACTIVE)->count();
            if ($active >= $capacity) {
                return back()->with('error', 'هذه المجموعة مكتملة العدد حالياً.');
            }
        }

        try {
            Enrollment::updateOrCreate(
                ['student_id' => $studentId, 'batch_id' => $batch->id],
                ['status' => Enrollment::STATUS_PENDING, 'enrollment_date' => now()->toDateString()]
            );
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', 'لديك طلب تسجيل قائم في هذه المجموعة بالفعل.');
        }

        return back()->with('success', 'تم إرسال طلب التسجيل الأولي بنجاح، وسيراجعه المعهد ويؤكده قريباً.');
    }

    
    private function openBatches()
    {
        return Batch::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()));
    }
}
