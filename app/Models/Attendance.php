<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const STATUSES = ['present', 'absent', 'late'];

    protected $fillable = [
        'batch_id',
        'student_id',
        'teacher_id',
        'date',
        'attendance_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'attendance_date' => 'date:Y-m-d',
    ];

    
    protected static function booted(): void
    {
        static::saving(function (Attendance $a) {
            $a->date = $a->date ?? $a->attendance_date;
            $a->attendance_date = $a->attendance_date ?? $a->date;
        });
    }

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
}
