<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Classroom extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'major_id',
        'grade_level',
        'name',
    ];

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function homeroomAssignments(): HasMany
    {
        return $this->hasMany(HomeroomAssignment::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('domain')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
