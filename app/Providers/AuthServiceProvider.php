<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Major;
use App\Models\SchoolYear;
use \App\Models\Classroom;
use \App\Models\HomeroomAssignment;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy;
use App\Policies\MajorPolicy;
use App\Policies\SchoolYearPolicy;
use \App\Policies\ClassroomPolicy;
use \App\Policies\HomeroomAssignmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Student::class => StudentPolicy::class,
        Teacher::class => TeacherPolicy::class,
        Major::class => MajorPolicy::class,
        SchoolYear::class => SchoolYearPolicy::class,
        Classroom::class => ClassroomPolicy::class,
        HomeroomAssignment::class => HomeroomAssignmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('viewMyClass', function ($user) {
            if (($user->role_label ?? null) !== 'wali_kelas') return false;

            $teacherId = $user->teacher_id; // jangan pakai relasi dulu
            if (!$teacherId) return false;

            $activeId = SchoolYear::activeId();
            if (!$activeId) return false;

            return HomeroomAssignment::query()
                ->where('school_year_id', $activeId)
                ->where('teacher_id', $teacherId)
                ->exists();
        });
    }

}
