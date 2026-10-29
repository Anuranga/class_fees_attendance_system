<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'role' => 'teacher',
        'status' => 'active',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'teacher_id',
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isActive(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return ($this->status ?? 'active') === 'active';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function effectiveTeacherId(): int
    {
        return $this->isAssistant() ? (int) $this->teacher_id : (int) $this->id;
    }

    public function effectiveTeacher(): ?User
    {
        return $this->isAssistant() ? $this->teacher : $this;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assistants(): HasMany
    {
        return $this->hasMany(User::class, 'teacher_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'teacher_id');
    }

    public function attendancesMarked(): HasMany
    {
        return $this->hasMany(Attendance::class, 'marked_by');
    }
}
