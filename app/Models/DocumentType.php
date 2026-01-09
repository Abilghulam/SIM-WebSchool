<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'for',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function studentDocuments(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function teacherDocuments(): HasMany
    {
        return $this->hasMany(TeacherDocument::class);
    }
}
