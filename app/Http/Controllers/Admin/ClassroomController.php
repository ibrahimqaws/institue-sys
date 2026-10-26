<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassroomController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Classrooms/Index', ['classrooms' => Classroom::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|string|in:lab,hall,workshop,lecture',
        ]);
        Classroom::create($validated);
        return redirect()->route('admin.classrooms.index')->with('success', 'تم إضافة القاعة بنجاح');
    }

    public function update(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'type' => 'required|string|in:lab,hall,workshop,lecture',
        ]);
        $classroom->update($validated);
        return redirect()->route('admin.classrooms.index')->with('success', 'تم تحديث القاعة بنجاح');
    }

    public function destroy(Classroom $classroom)
    {
        if ($classroom->batches()->exists()) {
            return back()->with('error', 'لا يمكن حذف قاعة مرتبطة بمجموعات دراسية. غيّر قاعة المجموعات أولاً.');
        }

        $classroom->delete();
        return redirect()->route('admin.classrooms.index')->with('success', 'تم حذف القاعة بنجاح');
    }
}