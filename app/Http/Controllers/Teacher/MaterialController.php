<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    
    public function index(Batch $batch)
    {
        $this->ensureTeacherOwnsBatch($batch);

        $materials = $batch->materials()->latest()->get()->map(fn (Material $m) => [
            'id' => $m->id,
            'title' => $m->title,
            'description' => $m->description,
            'type' => $m->type,
            'has_file' => (bool) $m->file_path,
            'file_name' => $m->file_path ? basename($m->file_path) : null,
            'due_date' => optional($m->due_date)->format('Y-m-d\TH:i'),
            'created_at' => optional($m->created_at)->format('Y-m-d'),
        ]);

        return response()->json(['materials' => $materials]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:file,assignment',
            'file' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg|max:10240',
            'due_date' => 'nullable|date',
        ]);

        $batch = $this->ensureTeacherOwnsBatch((int) $data['batch_id']);

        if ($data['type'] === 'file' && ! $request->hasFile('file')) {
            return back()->withErrors(['file' => 'أرفق ملفاً للمادة العلمية.']);
        }

        Material::create([
            'batch_id' => $batch->id,
            'teacher_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'file_path' => $request->hasFile('file') ? $request->file('file')->store('materials', Material::DISK) : null,
            'due_date' => $data['type'] === 'assignment' ? ($data['due_date'] ?? null) : null,
        ]);

        return back()->with('success', 'تم إرفاق المادة/الواجب بنجاح');
    }

    
    public function update(Request $request, Material $material)
    {
        $this->ensureTeacherOwnsBatch((int) $material->batch_id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg|max:10240',
        ]);

        $update = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $material->type === 'assignment' ? ($data['due_date'] ?? null) : null,
        ];

        if ($request->hasFile('file')) {
            $material->deleteStoredFile();
            $update['file_path'] = $request->file('file')->store('materials', Material::DISK);
        }

        $material->update($update);

        return back()->with('success', 'تم تعديل المادة بنجاح');
    }

    public function destroy(Material $material)
    {
        $this->ensureTeacherOwnsBatch((int) $material->batch_id);

        $material->deleteStoredFile();
        $material->delete();

        return back()->with('success', 'تم حذف المادة');
    }
}
