<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:9999999999',
            'duration_hours' => 'required|integer|min:1',
        ]);

        Course::create($validated);

        return redirect()->route('admin.courses.index')->with('success', 'تم إضافة الكورس بنجاح');
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0|max:9999999999',
            'duration_hours' => 'required|integer|min:1',
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')->with('success', 'تم تحديث الكورس بنجاح');
    }

    public function destroy(Course $course)
    {
        if ($course->batches()->exists()) {
            return back()->with('error', 'لا يمكن حذف كورس له مجموعات دراسية. احذف المجموعات أو أنهِها أولاً.');
        }

        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'تم حذف الكورس بنجاح');
    }
}