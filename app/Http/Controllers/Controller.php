<?php

namespace App\Http\Controllers;

use App\Models\Batch;

abstract class Controller
{
    
    protected function ensureTeacherOwnsBatch(Batch|int $batch): Batch
    {
        $batch = $batch instanceof Batch ? $batch : Batch::findOrFail($batch);

        abort_unless(
            (int) $batch->teacher_id === (int) auth()->id(),
            403,
            'هذه المجموعة ليست من مجموعاتك.'
        );

        return $batch;
    }

    
    protected function ensureStudentInBatch(Batch $batch, int $studentId): void
    {
        abort_unless(
            $batch->students()->where('users.id', $studentId)->exists(),
            422,
            'هذا الطالب غير مسجل في المجموعة.'
        );
    }
}
