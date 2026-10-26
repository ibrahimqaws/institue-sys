<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\StudentNote;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    
    public function index(Request $request, Batch $batch)
    {
        $this->ensureTeacherOwnsBatch($batch);

        $notes = StudentNote::where('batch_id', $batch->id)
            ->where('teacher_id', $request->user()->id)
            ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->student_id))
            ->with('student:id,name')
            ->latest()
            ->get()
            ->map(fn (StudentNote $n) => [
                'id' => $n->id,
                'student_id' => $n->student_id,
                'student_name' => $n->student?->name,
                'type' => $n->type,
                'content' => $n->content,
                'created_at' => optional($n->created_at)->format('Y-m-d'),
            ]);

        return response()->json(['notes' => $notes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'batch_id' => 'required|exists:batches,id',
            'type' => 'required|in:academic,behavioral',
            'content' => 'required|string|max:1000',
        ]);

        $batch = $this->ensureTeacherOwnsBatch((int) $data['batch_id']);
        $this->ensureStudentInBatch($batch, (int) $data['student_id']);

        StudentNote::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $request->user()->id,
            'batch_id' => $batch->id,
            'type' => $data['type'],
            'content' => $data['content'],
        ]);

        return back()->with('success', 'تم حفظ الملاحظة بنجاح');
    }

    public function update(Request $request, StudentNote $note)
    {
        $this->ensureOwnNote($note);

        $data = $request->validate([
            'type' => 'required|in:academic,behavioral',
            'content' => 'required|string|max:1000',
        ]);

        $note->update($data);

        return back()->with('success', 'تم تعديل الملاحظة');
    }

    public function destroy(StudentNote $note)
    {
        $this->ensureOwnNote($note);
        $note->delete();

        return back()->with('success', 'تم حذف الملاحظة');
    }

    
    private function ensureOwnNote(StudentNote $note): void
    {
        abort_unless((int) $note->teacher_id === (int) auth()->id(), 403, 'هذه الملاحظة ليست من ملاحظاتك.');
    }
}
