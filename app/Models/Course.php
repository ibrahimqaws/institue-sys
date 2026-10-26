<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['title', 'description', 'price', 'duration_hours'];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_hours' => 'integer',
    ];

    public function batches(): HasMany { return $this->hasMany(Batch::class); }
}
