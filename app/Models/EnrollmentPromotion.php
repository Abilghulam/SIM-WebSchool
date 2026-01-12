<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EnrollmentPromotionItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnrollmentPromotion extends Model
{
    protected $fillable = [
        'from_school_year_id',
        'to_school_year_id',
        'executed_by',
        'executed_at',
        'mapping_json',
        'total_students',
        'moved_students',
        'graduated_students',
        'skipped_students',
        'status',
        'error_message',
    ];

    protected $casts = [
        'mapping_json' => 'array',
        'executed_at' => 'datetime',
    ];

    public function fromYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'from_school_year_id');
    }

    public function toYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'to_school_year_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EnrollmentPromotionItem::class, 'enrollment_promotion_id');
    }
}
