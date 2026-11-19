<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaperMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'paper_id',
        'student_id',
        'marks',
        'is_absent',
        'remarks',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'marks' => 'decimal:2',
            'is_absent' => 'boolean',
        ];
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class, 'paper_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getPercentageAttribute(): ?float
    {
        if ($this->is_absent || $this->marks === null || ! $this->paper || (float) $this->paper->max_marks <= 0) {
            return null;
        }

        return round(((float) $this->marks / (float) $this->paper->max_marks) * 100, 1);
    }

    public function getGradeAttribute(): string
    {
        return Paper::gradeFromPercentage($this->percentage);
    }
}
