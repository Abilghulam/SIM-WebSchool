<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_locked',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function homeroomAssignments(): HasMany
    {
        return $this->hasMany(HomeroomAssignment::class);
    }

    public static function activeId(): ?int
    {
        return static::query()->where('is_active', true)->value('id');
    }

    public function lock(): void
    {
        $this->forceFill(['is_locked' => true])->save();
    }

    public function unlock(): void
    {
        $this->forceFill(['is_locked' => false])->save();
    }


}
