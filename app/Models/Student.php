<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SchoolYear;
use App\Models\HomeroomAssignment;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nis',
        'full_name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'phone',
        'email',
        'address',
        'father_name',
        'mother_name',
        'guardian_name',
        'parent_phone',
        'status',
        'entry_year',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // Enrollment aktif (berguna untuk "kelas aktif sekarang")
    public function activeEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)
            ->where('is_active', true)
            ->latestOfMany(); // ambil yang terbaru jika ada lebih dari 1
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function scopeVisibleTo(Builder $query, \App\Models\User $user): Builder
    {
        // Admin/TU: bisa lihat semua
        if ($user->canManageSchoolData() || $user->isPimpinan()) {
            return $query;
        }

        // Wali kelas: hanya siswa di kelas yang dia pegang pada TA aktif
        if ($user->isWaliKelas()) {
            $activeYearId = SchoolYear::activeId();
            if (!$activeYearId || !$user->teacher_id) {
                // tidak ada TA aktif / user tidak terhubung ke teacher => jangan tampilkan apa pun
                return $query->whereRaw('1=0');
            }

            $classroomIds = HomeroomAssignment::query()
                ->where('school_year_id', $activeYearId)
                ->where('teacher_id', $user->teacher_id)
                ->pluck('classroom_id');

            return $query->whereHas('enrollments', function (Builder $e) use ($activeYearId, $classroomIds) {
                $e->where('school_year_id', $activeYearId)
                  ->whereIn('classroom_id', $classroomIds)
                  ->where('is_active', true);
            });
        }

        // Guru biasa: default tidak bisa lihat siswa
        return $query->whereRaw('1=0');
    }
}
