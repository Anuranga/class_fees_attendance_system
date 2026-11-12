<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SchoolSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'registration_token',
        'name',
        'motto',
        'logo_path',
        'vision',
        'mission',
        'about_us',
        'principal_message',
        'items_to_bring_sinhala',
        'items_to_bring_english',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'school_setting_id');
    }

    protected static function booted()
    {
        static::creating(function ($school) {
            if (empty($school->registration_token)) {
                $school->registration_token = Str::random(24);
            }
            if (empty($school->slug)) {
                $school->slug = Str::slug($school->name).'-'.Str::random(5);
            }
        });
    }

    /**
     * Get the default Sinhala items-to-bring checklist.
     */
    public static function defaultSinhalaItems(): string
    {
        return "1. උප්පැන්න සහතිකයේ ඡායා පිටපතක් (Copy of Birth Certificate)\n"
            ."2. දරුවාගේ පාස්පෝට් ප්‍රමාණයේ ඡායාරූප 2 ක් (2 Passport size photos)\n"
            ."3. දරුවාගේ එන්නත් කාඩ්පතේ පිටපතක් (Child Immunization Card copy)\n"
            ."4. පානීය ජල බෝතලයක් (Water bottle with name tag)\n"
            ."5. අමතර ඇඳුම් කට්ටලයක් (Extra set of clothes)\n"
            ."6. කුඩා තුවායක් සහ අත් පිස්නාවක් (Small towel/handkerchief)\n"
            .'7. දහවල් ආහාර පෙට්ටිය (Nutritious snack box)';
    }

    /**
     * SaaS Multi-tenant resolver: Get setting for current logged-in Principal, or fall back to first school.
     */
    public static function get(?User $user = null): self
    {
        $user = $user ?? Auth::user();

        if ($user) {
            $principalId = $user->isAdmin() ? $user->id : ($user->teacher_id ?: $user->id);
            $school = static::where('user_id', $principalId)->first();
            if ($school) {
                if (empty($school->registration_token)) {
                    $school->registration_token = Str::random(24);
                    $school->save();
                }

                return $school;
            }
        }

        // Return first or create default
        $school = static::firstOrCreate([], [
            'name' => 'Little Stars International Pre-School',
            'motto' => 'Nurturing Little Minds for a Brighter Tomorrow',
            'registration_token' => Str::random(24),
            'vision' => 'To be a center of early childhood educational excellence where every child discovers joy in learning, creativity, and self-confidence.',
            'mission' => 'We provide a loving, safe, and stimulating environment guided by holistic early learning methodologies, empowering every child through play, exploration, and character foundation.',
            'about_us' => 'Welcome to Little Stars International Pre-School. We offer high-quality early childhood care and preschool education for Playgroup, Nursery, LKG, UKG, and Daycare.',
            'principal_message' => 'Every child is unique and full of potential. Our dedicated team of certified educators ensures a warm, joyful start to your child’s educational journey.',
            'items_to_bring_sinhala' => static::defaultSinhalaItems(),
            'contact_phone' => '+94 77 123 4567',
            'whatsapp_number' => '+94 77 123 4567',
            'contact_email' => 'info@littlestars.lk',
            'address' => '123 Temple Road, Colombo, Sri Lanka',
            'academic_year' => '2026/2027',
            'is_admission_open' => true,
        ]);

        if (empty($school->registration_token)) {
            $school->registration_token = Str::random(24);
            $school->save();
        }

        return $school;
    }

    /**
     * Sharable Registration URL for WhatsApp.
     */
    public function getRegistrationUrlAttribute(): string
    {
        return url('/apply/'.$this->registration_token);
    }
}
