<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_number',
        'full_name',
        'contact_number',
        'guardian_name',
        'guardian_phone',
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_student', 'student_id', 'class_id')
            ->withPivot(['id', 'student_class_uid', 'qr_token', 'joined_via', 'status', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassStudent::class, 'student_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    /**
     * Generate next sequential admission number if not provided
     */
    public static function generateAdmissionNumber(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $nextId = $last ? ($last->id + 1) : 1;

        return 'ADM-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
