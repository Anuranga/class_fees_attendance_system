<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'motto',
        'logo_path',
        'vision',
        'mission',
        'about_us',
        'principal_message',
        'contact_phone',
        'whatsapp_number',
        'contact_email',
        'address',
        'academic_year',
        'is_admission_open',
    ];

    protected $casts = [
        'is_admission_open' => 'boolean',
    ];

    /**
     * Get or create the singleton school setting.
     */
    public static function get(): self
    {
        return static::firstOrCreate([], [
            'name' => 'Little Stars International Pre-School',
            'motto' => 'Nurturing Little Minds for a Brighter Tomorrow',
            'vision' => 'To be a center of early childhood educational excellence where every child discovers joy in learning, creativity, and self-confidence.',
            'mission' => 'We provide a loving, safe, and stimulating environment guided by holistic early learning methodologies, empowering every child through play, exploration, and character foundation.',
            'about_us' => 'Welcome to Little Stars International Pre-School. We offer high-quality early childhood care and preschool education for Playgroup, Nursery, LKG, UKG, and Daycare.',
            'principal_message' => 'Every child is unique and full of potential. Our dedicated team of certified educators ensures a warm, joyful start to your child’s educational journey.',
            'contact_phone' => '+94 77 123 4567',
            'whatsapp_number' => '+94 77 123 4567',
            'contact_email' => 'info@littlestars.lk',
            'address' => '123 Temple Road, Colombo, Sri Lanka',
            'academic_year' => '2026/2027',
            'is_admission_open' => true,
        ]);
    }
}
