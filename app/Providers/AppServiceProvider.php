<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\Schedule;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Policies\AssignmentPolicy;
use App\Policies\GradePolicy;
use App\Policies\MaterialPolicy;
use App\Policies\QuizPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\SchoolYearPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(LearningMaterial::class, MaterialPolicy::class);
        Gate::policy(Student::class, GradePolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(SchoolYear::class, SchoolYearPolicy::class);
    }
}
