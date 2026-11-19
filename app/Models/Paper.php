<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paper extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'paper_date',
        'max_marks',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'paper_date' => 'date',
            'max_marks' => 'decimal:2',
        ];
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(PaperMark::class, 'paper_id');
    }

    /**
     * Calculate average percentage for non-absent graded marks.
     */
    public function averagePercentage(): ?float
    {
        $graded = $this->marks->filter(fn (PaperMark $m): bool => ! $m->is_absent && $m->marks !== null);

        if ($graded->isEmpty() || (float) $this->max_marks <= 0) {
            return null;
        }

        $avgMarks = $graded->avg('marks');

        return round(($avgMarks / (float) $this->max_marks) * 100, 1);
    }

    /**
     * Calculate grade letter from percentage (Sri Lankan standard).
     */
    public static function gradeFromPercentage(?float $percentage): string
    {
        if ($percentage === null) {
            return 'AB';
        }

        if ($percentage >= 75) {
            return 'A';
        }

        if ($percentage >= 65) {
            return 'B';
        }

        if ($percentage >= 55) {
            return 'C';
        }

        if ($percentage >= 35) {
            return 'S';
        }

        return 'W';
    }
}
