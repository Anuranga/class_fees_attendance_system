<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniformItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_setting_id',
        'name',
        'size_options',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            if (empty($item->school_setting_id)) {
                $item->school_setting_id = SchoolSetting::get()->id;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(SchoolSetting::class, 'school_setting_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(UniformOrder::class, 'uniform_item_id');
    }
}
