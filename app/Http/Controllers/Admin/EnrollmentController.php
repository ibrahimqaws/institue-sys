<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['pending', 'active', 'dropped', 'completed'], true)
            ? $request->query('status') : 'pending';

        $enrollments = Enrollment::with(['student:id,name,email,phone', 'batch.course:id,title,price', 'batch.classroom:id,name,capacity'])
            ->where('status', $status)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => $enrollments,
            'status' => $status,
            'counts' => Enrollment::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'students' => User::where('role', User::ROLE_STUDENT)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'batches' => Batch::with('course:id,title')->where('status', 'active')->orderBy('name')->get(['id', 'name', 'course_id']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', Rule::exists('users', 'id')->where('role', User::ROLE_STUDENT)],
            'batch_id' => 'required|exists:batches,id',
        ]);

        $batch = Batch::with('classroom')->findOrFail($data['batch_id']);

        $existing = Enrollment::where('student_id', $data['student_id'])->where('batch_id', $batch->id)->first();

        if ($existing && in_array($existing->status, ['active', 'pending'], true)) {
            return back()->with('error', 'الطالب مسجل أو لديه طلب تسجيل قائم في هذه المجموعة.');
        }

        if ($error = $this->capacityError($batch, $existing?->id)) {
            return back()->with('error', $error);
        }

        Enrollment::updateOrCreate(
            ['student_id' => $data['student_id'], 'batch_id' => $batch->id],
            ['status' => Enrollment::STATUS_ACTIVE, 'enrollment_date' => now()->toDateString()]
        );

        return back()->with('success', 'تم تسجيل الطالب في المجموعة.');
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'dropped', 'completed'])],
        ]);

        if ($data['status'] === 'active' && $enrollment->status !== 'active') {
            if ($error = $this->capacityError($enrollment->batch, $enrollment->id)) {
                return back()->with('error', $error);
            }
        }

        $enrollment->update($data);

        return back()->with('success', 'تم تحديث حالة التسجيل.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $hasPayments = Payment::where('student_id', $enrollment->student_id)
            ->where('batch_id', $enrollment->batch_id)->exists();

        if ($hasPayments) {
            return back()->with('error', 'توجد دفعات مالية لهذا التسجيل، غيّر حالته إلى "منسحب" بدل الحذف.');
        }

        $enrollment->delete();

        return back()->with('success', 'تم حذف طلب التسجيل.');
    }

    private function capacityError(Batch $batch, ?int $ignoreEnrollmentId = null): ?string
    {
        $capacity = $batch->classroom?->capacity;

        if (! $capacity) {
            return null;
        }

        $active = Enrollment::where('batch_id', $batch->id)
            ->where('status', 'active')
            ->when($ignoreEnrollmentId, fn ($q) => $q->where('id', '!=', $ignoreEnrollmentId))
            ->count();

        return $active >= $capacity
            ? "القاعة ({$batch->classroom->name}) ممتلئة: السعة {$capacity} طالباً."
            : null;
    }
}
