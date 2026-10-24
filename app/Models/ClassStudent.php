<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ClassStudent extends Model
{
    use HasFactory;

    protected $table = 'class_student';

    protected $fillable = [
        'class_id',
        'student_id',
        'student_class_uid',
        'qr_token',
        'joined_via',
        'status',
    ];

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id')
            ->where('class_id', $this->class_id);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id', 'student_id')
            ->where('class_id', $this->class_id);
    }

    public function isPaidForMonth(string $month): bool
    {
        return Payment::where('class_id', $this->class_id)
            ->where('student_id', $this->student_id)
            ->where('payment_month', $month)
            ->exists();
    }

    public static function generateUid(int $classId, int $studentId): string
    {
        return 'CLS-'.$classId.'-STD-'.str_pad($studentId, 4, '0', STR_PAD_LEFT);
    }

    public static function generateQrToken(): string
    {
        return (string) Str::uuid();
    }
}
