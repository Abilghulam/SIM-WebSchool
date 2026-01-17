<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EnrollmentPromotion;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_label',
        'teacher_id',
        'is_active',
        'must_change_password',
        'last_login_at',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function createdStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'created_by');
    }

    public function updatedStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'updated_by');
    }

    public function createdTeachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'created_by');
    }

    public function updatedTeachers(): HasMany
    {
        return $this->hasMany(Teacher::class, 'updated_by');
    }

    public function uploadedStudentDocuments(): HasMany
    {
        return $this->hasMany(StudentDocument::class, 'uploaded_by');
    }

    public function uploadedTeacherDocuments(): HasMany
    {
        return $this->hasMany(TeacherDocument::class, 'uploaded_by');
    }

    public function hasRole(string $role): bool
    {
        return ($this->role_label ?? '') === $role;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role_label, ['admin'], true);
    }

    public function isOperator(): bool
    {
        return in_array($this->role_label, ['operator'], true);
    }

    public function isWaliKelas(): bool
    {
        return in_array($this->role_label, ['wali_kelas'], true);
    }

    public function isPimpinan(): bool
    {
        return in_array($this->role_label, ['pimpinan'], true);
    }

    public function isGuru(): bool
    {
        return in_array($this->role_label, ['guru'], true);
    }

    // untuk role yang boleh akses global data (bisa kamu tambah sesuai kebutuhan)
    public function canManageSchoolData(): bool
    {
        return $this->isAdmin() || $this->isOperator();
    }

    public function enrollmentPromotions(): HasMany
    {
        return $this->hasMany(EnrollmentPromotion::class, 'executed_by');
    }

    public function profilePhotoUrl(): ?string
    {
        if (!$this->profile_photo_path) return null;
        return asset('storage/' . ltrim($this->profile_photo_path, '/'));
    }

    public function avatarInitials(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') return '?';

        $parts = preg_split('/\s+/', $name) ?: [];
        $first = mb_substr($parts[0] ?? '', 0, 1);
        $second = '';

        if (count($parts) >= 2) {
            $second = mb_substr($parts[1] ?? '', 0, 1);
        } else {
            // kalau satu kata, ambil huruf ke-2 kalau ada
            $second = mb_substr($parts[0] ?? '', 1, 1);
        }

        $ini = mb_strtoupper($first . $second);
        return $ini !== '' ? $ini : '?';
    }

    public function avatarColorClass(): string
    {
        // konsisten berdasarkan hash email/username/id
        $seed = (string) ($this->email ?? $this->username ?? $this->id ?? 'user');
        $hash = crc32($seed);

        $classes = [
            'bg-slate-600',
            'bg-gray-600',
            'bg-zinc-600',
            'bg-stone-600',
            'bg-red-600',
            'bg-orange-600',
            'bg-amber-600',
            'bg-yellow-600',
            'bg-lime-600',
            'bg-green-600',
            'bg-emerald-600',
            'bg-teal-600',
            'bg-cyan-600',
            'bg-sky-600',
            'bg-blue-600',
            'bg-indigo-600',
            'bg-violet-600',
            'bg-purple-600',
            'bg-fuchsia-600',
            'bg-pink-600',
            'bg-rose-600',
        ];

        return $classes[$hash % count($classes)];
    }


}
