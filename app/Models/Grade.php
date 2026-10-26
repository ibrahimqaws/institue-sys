<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'batch_id',
        'student_id',
        'title',
        'exam_name',
        'score',
        'student_score',
        'max_score',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'student_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    protected $appends = ['percentage'];

    
    protected static function booted(): void
    {
        static::saving(function (Grade $g) {
            $g->exam_name = $g->exam_name ?? $g->title;
            $g->title = $g->title ?? $g->exam_name;
            $g->student_score = $g->student_score ?? $g->score;
            $g->score = $g->score ?? $g->student_score;
        });
    }

    public function getPercentageAttribute(): ?float
    {
        $score = $this->score ?? $this->student_score;
        $max = (float) $this->max_score;

        return $max > 0 && $score !== null ? round(((float) $score / $max) * 100, 1) : null;
    }

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class, 'batch_id'); }
}
