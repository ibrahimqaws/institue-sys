<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    
    public function index(Request $request): Response
    {
        $batches = Batch::where('teacher_id', $request->user()->id)
            ->with('classroom:id,name')
            ->withCount(['students', 'materials'])
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Teacher/Dashboard', [
            'batches' => $batches->map(fn (Batch $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'subject_name' => $b->subject_name,
                'status' => $b->status,
                'days' => array_values(array_filter(array_map('trim', explode(',', (string) $b->days)))),
                'start_time' => $b->start_time ? substr($b->start_time, 0, 5) : null,
                'end_time' => $b->end_time ? substr($b->end_time, 0, 5) : null,
                'schedule_label' => $b->schedule_label,
                'room' => $b->classroom?->name ?? $b->room_number,
                'start_date' => optional($b->start_date)->format('Y-m-d'),
                'end_date' => optional($b->end_date)->format('Y-m-d'),
                'students_count' => $b->students_count,
                'materials_count' => $b->materials_count,
            ])->values(),
            'dayNames' => Batch::DAYS,
        ]);
    }
}
