<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialDownloadController extends Controller
{
    public function __invoke(Request $request, Material $material): StreamedResponse
    {
        $allowed = Enrollment::where('student_id', $request->user()->id)
            ->where('batch_id', $material->batch_id)
            ->whereIn('status', [Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
            ->exists();

        abort_unless($allowed, 403, 'ليست لديك صلاحية لتنزيل هذا الملف.');

        $disk = $material->resolveDisk();

        abort_unless($disk, 404, 'الملف غير متوفر.');

        $extension = pathinfo($material->file_path, PATHINFO_EXTENSION);
        $safeTitle = trim(preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/u', '-', $material->title)) ?: 'material';
        $name = $extension ? "{$safeTitle}.{$extension}" : $safeTitle;

        return Storage::disk($disk)->download($material->file_path, $name);
    }
}
