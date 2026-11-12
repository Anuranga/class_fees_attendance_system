<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'can_collect_fees',
        'can_manage_uniforms',
    ];

    protected $casts = [
        'can_collect_fees' => 'boolean',
        'can_manage_uniforms' => 'boolean',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'can_collect_fees' => 'boolean',
            'can_manage_uniforms' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'principal';
    }

    public function isPrincipal(): bool
    {
        return $this->isAdmin();
    }

    public function canCollectFees(): bool
    {
        return $this->isAdmin() || (bool) $this->can_collect_fees;
    }

    public function canManageUniforms(): bool
    {
        return $this->isAdmin() || (bool) $this->can_manage_uniforms;
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

    public function isPending(): bool
    {
        return $this->status === 'pending';
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

    public function assignedClasses(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'assistant_class', 'user_id', 'class_id')
            ->withTimestamps();
    }

    /**
     * Get the Eloquent query builder for classes this user is allowed to access.
     */
    public function accessibleClassesQuery(): Builder
    {
        if ($this->isAssistant()) {
            $assignedIds = $this->assignedClasses()->pluck('classes.id')->toArray();

            return ClassRoom::where('teacher_id', $this->effectiveTeacherId())
                ->whereIn('classes.id', $assignedIds);
        }

        return ClassRoom::where('teacher_id', $this->effectiveTeacherId());
    }

    /**
     * Get the array of class IDs this user is allowed to access.
     *
     * @return array<int, int>
     */
    public function accessibleClassIds(): array
    {
        return $this->accessibleClassesQuery()->pluck('classes.id')->toArray();
    }

    /**
     * Determine whether this user can access a specific class.
     */
    public function canAccessClass(ClassRoom $class): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($class->teacher_id !== $this->effectiveTeacherId()) {
            return false;
        }

        if ($this->isAssistant()) {
            return $this->assignedClasses()->where('classes.id', $class->id)->exists();
        }

        return true;
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
