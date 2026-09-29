<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\Strand;
use App\Models\Student;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrackGuardianModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_strand_belongs_to_track_and_track_has_many_strands(): void
    {
        $track = Track::create([
            'track_code' => 'ACAD', 'track_name' => 'Academic Track',
        ]);

        $strand = Strand::create([
            'track_id' => $track->track_id, 'strand_code' => 'STEM', 'strand_name' => 'STEM',
        ]);

        // Regression: Strand::track() threw class-not-found while App\Models\Track was missing.
        $this->assertSame($track->track_id, $strand->track?->track_id);
        $this->assertSame($strand->strand_id, $track->strands?->first()?->strand_id);
    }

    public function test_guardian_belongs_to_user_and_links_to_students_via_fk(): void
    {
        $guardianUser = User::create([
            'first_name' => 'Parent', 'last_name' => 'One', 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Guardian', 'status' => 'Active',
        ]);
        $guardian = Guardian::create([
            'user_id' => $guardianUser->user_id, 'full_name' => 'Parent One', 'relationship' => 'Mother',
        ]);

        $studentOne = $this->makeStudent('111111111111', 'S-0001', $guardian->guardian_id);
        $studentTwo = $this->makeStudent('222222222222', 'S-0002', $guardian->guardian_id);

        $this->assertSame($guardianUser->user_id, $guardian->user?->user_id);
        $this->assertSame($guardian->guardian_id, $studentOne->guardian?->guardian_id);
        $this->assertSame($guardian->guardian_id, $studentTwo->guardian?->guardian_id);
        $this->assertSame(2, $guardian->students?->count());
    }

    public function test_student_guardian_link_is_optional(): void
    {
        $student = $this->makeStudent('333333333333', 'S-0003', null);

        $this->assertNull($student->guardian);
        $this->assertNull($student->guardian_id);
    }

    private function makeStudent(string $lrn, string $studentNumber, $guardianId): Student
    {
        $studentUser = User::create([
            'first_name' => 'Kid', 'last_name' => uniqid(), 'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'), 'must_change_password' => false,
            'role' => 'Student', 'status' => 'Active',
        ]);

        return Student::create([
            'user_id' => $studentUser->user_id, 'lrn' => $lrn, 'student_number' => $studentNumber,
            'grade_level' => '11', 'guardian_id' => $guardianId,
        ]);
    }
}