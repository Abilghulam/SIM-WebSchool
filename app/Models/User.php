<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

}
