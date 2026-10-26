<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DROPPED = 'dropped';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = ['student_id', 'batch_id', 'enrollment_date', 'status'];

    protected $casts = ['enrollment_date' => 'date:Y-m-d'];

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }

    
    public function totalFee(): float
    {
        return (float) ($this->batch?->course?->price ?? 0);
    }

    
    public function totalPaid(): float
    {
        return (float) Payment::where('student_id', $this->student_id)
            ->where('batch_id', $this->batch_id)
            ->sum('amount_paid');
    }

    
    public function remaining(): float
    {
        return max(0, $this->totalFee() - $this->totalPaid());
    }
}
