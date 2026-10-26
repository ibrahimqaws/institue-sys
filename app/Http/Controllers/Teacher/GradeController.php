<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    
    public function showBatchStudents(Batch $batch)
    {
        $this->ensureTeacherOwnsBatch($batch);

        $students = $batch->students()
            ->with(['grades' => fn ($q) => $q->where('batch_id', $batch->id)->orderByDesc('id')])
            ->get(['users.id', 'users.name']);

        return response()->json(['students' => $students]);
    }

    
    public function store(Request $request)
    {
        $data = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:1',
        ]);

        if ((float) $data['score'] > (float) $data['max_score']) {
            return back()->withErrors(['score' => 'الدرجة لا يمكن أن تتجاوز الدرجة العظمى.']);
        }

        $batch = $this->ensureTeacherOwnsBatch((int) $data['batch_id']);
        $this->ensureStudentInBatch($batch, (int) $data['student_id']);

        Grade::updateOrCreate(
            ['batch_id' => $batch->id, 'student_id' => $data['student_id'], 'title' => $data['title']],
            [
                'exam_name' => $data['title'],
                'score' => $data['score'],
                'student_score' => $data['score'],
                'max_score' => $data['max_score'],
            ]
        );

        return back()->with('success', 'تم حفظ الدرجة بنجاح');
    }

    
    public function update(Request $request, Grade $grade)
    {
        $this->ensureTeacherOwnsBatch((int) $grade->batch_id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:1',
        ]);

        if ((float) $data['score'] > (float) $data['max_score']) {
            return back()->withErrors(['score' => 'الدرجة لا يمكن أن تتجاوز الدرجة العظمى.']);
        }

        $duplicate = Grade::where('batch_id', $grade->batch_id)
            ->where('student_id', $grade->student_id)
            ->where('title', $data['title'])
            ->where('id', '!=', $grade->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['title' => 'لدى الطالب تقييم بنفس هذا العنوان مسبقاً.']);
        }

        $grade->update([
            'title' => $data['title'],
            'exam_name' => $data['title'],
            'score' => $data['score'],
            'student_score' => $data['score'],
            'max_score' => $data['max_score'],
        ]);

        return back()->with('success', 'تم تعديل الدرجة بنجاح');
    }

    
    public function destroy(Grade $grade)
    {
        $this->ensureTeacherOwnsBatch((int) $grade->batch_id);
        $grade->delete();

        return back()->with('success', 'تم حذف الدرجة');
    }
}
