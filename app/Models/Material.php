<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    
    public const DISK = 'local';

    
    private const LEGACY_DISK = 'public';

    protected $fillable = ['batch_id', 'teacher_id', 'title', 'description', 'type', 'file_path', 'due_date'];

    protected $casts = ['due_date' => 'datetime'];

    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }

    
    public function resolveDisk(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            if (Storage::disk($disk)->exists($this->file_path)) {
                return $disk;
            }
        }

        return null;
    }

    
    public function deleteStoredFile(): void
    {
        if (! $this->file_path) {
            return;
        }

        Storage::disk(self::DISK)->delete($this->file_path);
        Storage::disk(self::LEGACY_DISK)->delete($this->file_path);
    }
}
