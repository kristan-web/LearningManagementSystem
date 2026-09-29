<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attempt counts are now enforced at the application level (abort_unless in
 * StudentQuizController against quizzes.attempts_allowed), since a student
 * may legitimately have more than one row per quiz once retakes are allowed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add the replacement index before dropping the unique one — MySQL needs
        // continuous index coverage on quiz_id for its foreign key constraint.
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index(['quiz_id', 'student_id'], 'idx_attempt_quiz_student');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropUnique('uq_attempt_once');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->unique(['quiz_id', 'student_id'], 'uq_attempt_once');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_attempt_quiz_student');
        });
    }
};
