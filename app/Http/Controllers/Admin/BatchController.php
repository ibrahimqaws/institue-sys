<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $batches = Batch::with(['course:id,title,price', 'classroom:id,name,capacity', 'teacher:id,name'])
            ->withCount(['enrollments as students_count' => fn ($q) => $q->where('status', 'active')])
            ->when(in_array($status, ['active', 'completed', 'cancelled'], true), fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->get();

        return Inertia::render('Admin/Batches/Index', [
            'batches' => $batches,
            'courses' => Course::orderBy('title')->get(['id', 'title']),
            'classrooms' => Classroom::orderBy('name')->get(['id', 'name', 'capacity']),
            'teachers' => User::where('role', User::ROLE_TEACHER)->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'days' => Batch::DAYS,
            'status' => $status,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($error = $this->conflictError($data)) {
            return back()->withErrors(['schedule' => $error])->withInput();
        }

        Batch::create($data);

        return redirect()->route('admin.batches.index')->with('success', 'تم إنشاء المجموعة بنجاح');
    }

    public function update(Request $request, Batch $batch)
    {
        $data = $this->validated($request);

        if ($error = $this->conflictError($data, $batch->id)) {
            return back()->withErrors(['schedule' => $error])->withInput();
        }

        $batch->update($data);

        return redirect()->route('admin.batches.index')->with('success', 'تم تحديث المجموعة بنجاح');
    }

    public function destroy(Batch $batch)
    {
        if ($batch->enrollments()->exists() || $batch->payments()->exists() || $batch->attendances()->exists() || $batch->grades()->exists()) {
            return back()->with('error', 'للمجموعة تسجيلات أو سجلات مرتبطة. غيّر حالتها إلى "ملغاة" أو "منتهية" بدل الحذف.');
        }

        $batch->delete();

        return redirect()->route('admin.batches.index')->with('success', 'تم حذف المجموعة');
    }

    private function validated(Request $request): array
    {
        foreach (['start_time', 'end_time'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => substr($request->input($field), 0, 5)]);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => ['required', Rule::exists('users', 'id')->where('role', User::ROLE_TEACHER)],
            'classroom_id' => 'nullable|exists:classrooms,id',
            'days' => 'required|array|min:1',
            'days.*' => ['required', Rule::in(array_keys(Batch::DAYS))],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => ['nullable', Rule::in(['active', 'completed', 'cancelled'])],
        ]);

        $data['days'] = implode(',', array_values(array_unique($data['days'])));
        $data['status'] = $data['status'] ?? 'active';

        return $data;
    }

    
    private function conflictError(array $data, ?int $ignoreId = null): ?string
    {
        if ($data['status'] !== 'active') {
            return null;
        }

        $days = explode(',', $data['days']);

        $others = Batch::where('status', 'active')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereNotNull('start_time')
            ->where(function ($q) use ($data) {
                $q->where('teacher_id', $data['teacher_id']);
                if (! empty($data['classroom_id'])) {
                    $q->orWhere('classroom_id', $data['classroom_id']);
                }
            })
            ->get();

        foreach ($others as $other) {
            $sharedDays = array_intersect($days, explode(',', (string) $other->days));
            if (! $sharedDays) {
                continue;
            }

            $otherStart = substr($other->start_time, 0, 5);
            $otherEnd = substr((string) ($other->end_time ?? '23:59'), 0, 5);
            if (! ($data['start_time'] < $otherEnd && $data['end_time'] > $otherStart)) {
                continue;
            }

            $otherFrom = $other->start_date?->toDateString();
            $otherTo = $other->end_date?->toDateString();
            $from = $data['start_date'];
            $to = $data['end_date'] ?? null;
            $datesOverlap = (! $to || ! $otherFrom || $otherFrom <= $to) && (! $otherTo || $otherTo >= $from);
            if (! $datesOverlap) {
                continue;
            }

            $who = (int) $other->teacher_id === (int) $data['teacher_id'] ? 'المدرس' : 'القاعة';

            return "تعارض في الجدول: {$who} مرتبط بالمجموعة «{$other->name}» في نفس الأيام والوقت ({$otherStart} - {$otherEnd}).";
        }

        return null;
    }
}
