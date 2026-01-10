<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nip',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
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

    // akun login guru (1 teacher bisa punya 0 atau 1 user)
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'teacher_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }

    public function homeroomAssignments(): HasMany
    {
        return $this->hasMany(HomeroomAssignment::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        // Admin/Operator/Pimpinan boleh lihat semua data guru
        if ($user->canManageSchoolData() || $user->isPimpinan()) {
            return $query;
        }

        // Guru hanya boleh lihat data dirinya sendiri (berdasarkan user.teacher_id)
        if ($user->isGuru() && $user->teacher_id) {
            return $query->where('id', $user->teacher_id);
        }

        // Wali kelas juga guru; kita putuskan: wali kelas boleh lihat data dirinya sendiri saja
        if ($user->isWaliKelas() && $user->teacher_id) {
            return $query->where('id', $user->teacher_id);
        }

        return $query->whereRaw('1=0');
    }
}
