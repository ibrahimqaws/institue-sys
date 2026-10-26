<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'student';
    public const ROLES = [self::ROLE_ADMIN, self::ROLE_TEACHER, self::ROLE_STUDENT];

    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'salary',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'salary' => 'decimal:2',
        ];
    }

    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
    public function isTeacher(): bool { return $this->role === self::ROLE_TEACHER; }
    public function isStudent(): bool { return $this->role === self::ROLE_STUDENT; }

    // ---------- Scopes ----------
    public function scopeRole($query, string $role) { return $query->where('role', $role); }
    public function scopeActive($query) { return $query->where('is_active', true); }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    
    public function batches(): BelongsToMany
    {
        return $this->belongsToMany(Batch::class, 'enrollments', 'student_id', 'batch_id')
            ->wherePivot('status', 'active')
            ->withPivot('status', 'enrollment_date')
            ->withTimestamps();
    }

    public function payments(): HasMany { return $this->hasMany(Payment::class, 'student_id'); }
    public function grades(): HasMany { return $this->hasMany(Grade::class, 'student_id'); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class, 'student_id'); }
    public function notes(): HasMany { return $this->hasMany(StudentNote::class, 'student_id'); }

    public function taughtBatches(): HasMany { return $this->hasMany(Batch::class, 'teacher_id'); }
    public function materials(): HasMany { return $this->hasMany(Material::class, 'teacher_id'); }
    public function payrolls(): HasMany { return $this->hasMany(Payroll::class, 'user_id'); }
}
