<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'teacher_id',
        'name',
        'grade_level',
        'monthly_fee',
        'registration_token',
        'is_registration_open',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'is_registration_open' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($classRoom) {
            if (empty($classRoom->registration_token)) {
                $classRoom->registration_token = Str::random(24);
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id')
            ->withPivot(['id', 'student_class_uid', 'qr_token', 'joined_via', 'status', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassStudent::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'class_id');
    }

    public function getJoinUrlAttribute(): string
    {
        return url('/join/'.$this->registration_token);
    }

    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}
