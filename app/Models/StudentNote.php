<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentNote extends Model
{
    protected $fillable = ['student_id', 'teacher_id', 'batch_id', 'type', 'content'];

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
}
