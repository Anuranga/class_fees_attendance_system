<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'child_full_name',
        'child_calling_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'photo_path',
        'target_grade_level',
        'father_name',
        'father_phone',
        'father_occupation',
        'mother_name',
        'mother_phone',
        'mother_occupation',
        'whatsapp_number',
        'email',
        'home_address',
        'medical_notes',
        'authorized_pickup_persons',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'assigned_class_id',
        'created_student_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function assignedClass(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'assigned_class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'created_student_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function generateApplicationNumber(): string
    {
        $year = date('Y');
        $last = static::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $next = $last ? ($last->id + 1) : 1;

        return 'APP-'.$year.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
