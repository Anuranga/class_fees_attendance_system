<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $attributes = [
        'payment_type' => 'monthly',
        'payment_method' => 'cash',
    ];

    protected $fillable = [
        'class_id',
        'student_id',
        'teacher_id',
        'payment_type',
        'collected_by',
        'amount',
        'payment_month',
        'paid_date',
        'receipt_no',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function isDaily(): bool
    {
        return $this->payment_type === 'daily';
    }

    public function isMonthly(): bool
    {
        return $this->payment_type !== 'daily';
    }

    public static function generateReceiptNumber(): string
    {
        return 'RCP-'.date('Ym').'-'.strtoupper(Str::random(6));
    }
}
