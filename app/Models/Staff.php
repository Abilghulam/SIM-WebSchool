<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Staff extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'staff';

    protected $fillable = [
        'nip',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',

        'religion',
        'religion_other',
        'marital_status',

        'phone',
        'email',
        'address',
        'employment_status',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('domain')
            ->logOnly([
                'nip',
                'full_name',
                'gender',
                'birth_place',
                'birth_date',

                'religion',
                'religion_other',
                'marital_status',

                'phone',
                'email',
                'address',
                'employment_status',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'staff_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Staff {$eventName}";
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        // super admin / admin bisa lihat semua (sesuaikan dengan method kamu)
        if ($user->isAdmin()) return $query;

        // operator (TAS) hanya lihat dirinya sendiri
        if ($user->staff_id) {
            return $query->whereKey($user->staff_id);
        }

        return $query->whereKey([]);
    }
}
