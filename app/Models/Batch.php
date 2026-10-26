<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    public const DAYS = [
        'sat' => 'السبت', 'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء',
        'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة',
    ];

    protected $fillable = [
        'course_id',
        'teacher_id',
        'classroom_id',
        'name',
        'subject_name',
        'schedule_time',
        'days',
        'start_time',
        'end_time',
        'room_number',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $appends = ['schedule_label'];

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class, 'course_id'); }
    public function classroom(): BelongsTo { return $this->belongsTo(Classroom::class, 'classroom_id'); }

    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }

    
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'batch_id', 'student_id')
            ->wherePivot('status', 'active')
            ->withPivot('status', 'enrollment_date')
            ->withTimestamps();
    }

    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function grades(): HasMany { return $this->hasMany(Grade::class); }
    public function materials(): HasMany { return $this->hasMany(Material::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function notes(): HasMany { return $this->hasMany(StudentNote::class); }

    
    public function getScheduleLabelAttribute(): string
    {
        if ($this->days || $this->start_time) {
            $days = collect(explode(',', (string) $this->days))
                ->filter()
                ->map(fn ($d) => self::DAYS[trim($d)] ?? $d)
                ->implode('، ');

            $time = $this->start_time
                ? substr($this->start_time, 0, 5) . ($this->end_time ? ' - ' . substr($this->end_time, 0, 5) : '')
                : '';

            return trim($days . ($days && $time ? ' • ' : '') . $time);
        }

        return (string) ($this->schedule_time ?? '');
    }
}
