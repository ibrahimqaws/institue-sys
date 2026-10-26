<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Batch;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    
    public function showBatchStudents(Request $request, Batch $batch)
    {
        $this->ensureTeacherOwnsBatch($batch);

        $request->validate(['date' => 'nullable|date|before_or_equal:today']);
        $date = $request->input('date', now()->toDateString());

        $students = $batch->students()->get(['users.id', 'users.name', 'users.email']);

        $attendance = Attendance::where('batch_id', $batch->id)
            ->where('date', $date)
            ->get(['student_id', 'status', 'remarks'])
            ->keyBy('student_id');

        return response()->json([
            'date' => $date,
            'students' => $students,
            'attendance' => $attendance,
        ]);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'date' => 'nullable|date|before_or_equal:today',
            'records' => 'required|array|min:1',
            'records.*.status' => 'required|in:present,absent,late',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $batch = $this->ensureTeacherOwnsBatch((int) $request->batch_id);
        $date = $request->input('date', now()->toDateString());
        $teacherId = $request->user()->id;

        foreach ($request->records as $studentId => $data) {
            $this->ensureStudentInBatch($batch, (int) $studentId);

            Attendance::updateOrCreate(
                ['batch_id' => $batch->id, 'student_id' => $studentId, 'date' => $date],
                [
                    'attendance_date' => $date,
                    'teacher_id' => $teacherId,
                    'status' => $data['status'],
                    'remarks' => $data['remarks'] ?? null,
                ]
            );
        }

        return back()->with('success', 'تم حفظ الحضور والغياب بتاريخ ' . $date);
    }
}
