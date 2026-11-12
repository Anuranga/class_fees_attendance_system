<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UniformOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'student_id',
        'class_id',
        'uniform_item_id',
        'item_name',
        'size',
        'quantity',
        'unit_price',
        'total_amount',
        'payment_status',
        'paid_date',
        'receipt_no',
        'collected_by',
        'delivery_status',
        'delivered_date',
        'delivered_by',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_date' => 'date',
        'delivered_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function uniformItem(): BelongsTo
    {
        return $this->belongsTo(UniformItem::class, 'uniform_item_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public static function generateOrderNumber(): string
    {
        return 'UNF-'.date('Ym').'-'.strtoupper(Str::random(5));
    }

    /**
     * Sinhala message template when uniform payment is collected.
     */
    public function getSinhalaPaymentMessage(string $schoolName): string
    {
        $childName = $this->student->full_name;
        $amount = number_format($this->total_amount, 2);
        $itemName = $this->item_name;
        $size = $this->size ? " (ප්‍රමාණය: {$this->size})" : '';
        $receipt = $this->receipt_no ?: $this->order_number;

        return "ගෙවීම් තහවුරු කිරීම:\n"
            ."{$schoolName}\n"
            ."ගරු දෙමාපියනි,\n"
            ."ඔබගේ දරුවා වන {$childName} වෙනුවෙන් {$itemName}{$size} නිල ඇඳුම් මුදල වන රු. {$amount}/- මුදල සාර්ථකව ලැබී ඇත.\n"
            ."ලදුපත් අංකය: {$receipt}\n"
            ."නිල ඇඳුම සූදානම් වූ වහාම ඔබට දැනුම් දෙනු ලැබේ.\n"
            .'ස්තූතියි!';
    }

    /**
     * Sinhala message template when uniform is delivered/handed over.
     */
    public function getSinhalaDeliveryMessage(string $schoolName): string
    {
        $childName = $this->student->full_name;
        $itemName = $this->item_name;
        $date = $this->delivered_date ? $this->delivered_date->format('Y-m-d') : date('Y-m-d');

        return "නිල ඇඳුම් භාරදීමේ දැනුම්දීම:\n"
            ."{$schoolName}\n"
            ."ගරු දෙමාපියනි,\n"
            ."ඔබගේ දරුවා වන {$childName} ගේ {$itemName} නිල ඇඳුම {$date} දින පාසලෙන් සාර්ථකව භාර දෙන ලදී.\n"
            .'ස්තූතියි!';
    }
}
