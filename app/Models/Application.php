<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_setting_id',
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

    public function school(): BelongsTo
    {
        return $this->belongsTo(SchoolSetting::class, 'school_setting_id');
    }

    /**
     * Generate descriptive Sinhala WhatsApp Selection Message with Checklist of items to bring.
     */
    public function getSinhalaSelectionMessage(): string
    {
        $school = $this->school ?: SchoolSetting::get();
        $schoolName = $school->name;
        $childName = $this->child_full_name;
        $program = $this->target_grade_level;
        $academicYear = $school->academic_year;
        $items = $school->items_to_bring_sinhala ?: SchoolSetting::defaultSinhalaItems();

        return "සුභ පැතුම්! පෙරපාසල් ප්‍රවේශය තහවුරු කිරීම:\n"
            ."{$schoolName}\n"
            ."අධ්‍යයන වර්ෂය: {$academicYear}\n\n"
            ."ගරු දෙමාපියනි,\n"
            ."ඔබගේ දරුවා වන {$childName} ({$this->application_number}) අප පෙරපාසලේ '{$program}' පන්තිය සඳහා සාර්ථකව තෝරාගෙන ඇති බව ඉතා සතුටින් දන්වා සිටිමු.\n\n"
            ."පළමු දිනයේ රැගෙන ආ යුතු අත්‍යවශ්‍ය ද්‍රව්‍ය ලේඛනය:\n"
            ."{$items}\n\n"
            ."දරුවාගේ නිල ඩිජිටල් QR හැඳුනුම්පත් පාස්පත ලබා ගැනීමට:\n"
            .route('public.apply.status', ['query' => $this->application_number])."\n\n"
            ."වැඩිදුර විස්තර සඳහා අප හා සම්බන්ධ වන්න: {$school->contact_phone}\n"
            ."ස්තූතියි!\n"
            .'විදුහල්පතිතුමිය / පරිපාලනය';
    }

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
