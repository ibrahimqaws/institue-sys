<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id', 'period_month', 'base_amount', 'bonus', 'deductions',
        'net_amount', 'status', 'paid_at', 'expense_id', 'notes',
    ];

    protected $casts = [
        'period_month' => 'date:Y-m-d',
        'paid_at' => 'date:Y-m-d',
        'base_amount' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function expense(): BelongsTo { return $this->belongsTo(Expense::class); }
}
