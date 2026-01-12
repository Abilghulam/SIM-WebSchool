<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentPromotionItem extends Model
{
    protected $fillable = [
        'enrollment_promotion_id',
        'from_classroom_id',
        'to_classroom_id',
        'from_grade_level',
        'to_grade_level',
        'active_enrollments',
        'moved_students',
        'graduated_students',
        'skipped_students',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(EnrollmentPromotion::class, 'enrollment_promotion_id');
    }

    public function fromClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'from_classroom_id');
    }

    public function toClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'to_classroom_id');
    }
}
