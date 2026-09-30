<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\ClassSection;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Strand;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::create([
            'first_name' => 'Admin', 'last_name' => 'User',
            'email' => 'admin_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'Admin', 'status' => 'Active', 'must_change_password' => false,
        ]);
    }

    private function makeTeacher(): array
    {
        $user = User::create([
            'first_name' => 'Test', 'last_name' => 'Teacher',
            'email' => uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'Teacher', 'status' => 'Active', 'must_change_password' => false,
        ]);
        $teacher = Teacher::create([
            'user_id' => $user->user_id,
            'teacher_number' => 'T-' . uniqid(),
            'specialization' => 'Math',
        ]);
        return [$user, $teacher];
    }

    private function makeSection(): ClassSection
    {
        $trackId = DB::table('tracks')->insertGetId([
            'track_code' => 'ACAD-' . uniqid(), 'track_name' => 'Academic', 'created_at' => now(),
        ]);
        $strand = Strand::create([
            'track_id' => $trackId, 'strand_code' => 'STEM-' . uniqid(), 'strand_name' => 'STEM',
        ]);
        return ClassSection::create([
            'strand_id' => $strand->strand_id, 'grade_level' => '11',
            'section_name' => 'Sec-' . uniqid(), 'school_year' => '2026-2027',
            'max_slots' => 40, 'status' => 'Open',
        ]);
    }

    private function makeSubject(ClassSection $section): Subject
    {
        return Subject::create([
            'strand_id' => $section->strand_id,
            'subject_code' => 'SUBJ-' . uniqid(), 'subject_name' => 'Math',
            'subject_type' => 'Core', 'grade_level' => '11',
            'semester' => '1st Semester', 'units' => 1.0,
        ]);
    }

    private function makeRoom(): Room
    {
        return Room::create(['room_name' => 'Room-' . uniqid(), 'building' => 'Main', 'capacity' => 40]);
    }

    private function payload(ClassSection $section, Subject $subject, Teacher $teacher, ?Room $room = null): array
    {
        return [
            'section_id'  => $section->section_id,
            'subject_id'  => $subject->subject_id,
            'teacher_id'  => $teacher->teacher_id,
            'room_id'     => $room?->room_id,
            'day_of_week' => 'Monday',
            'start_time'  => '08:00',
            'end_time'    => '09:00',
        ];
    }

    /** @test */
    public function admin_can_view_schedule_index(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get('/admin/schedule');

        $response->assertOk();
        $response->assertViewIs('admin.schedule.index');
        $response->assertViewHas('schedules');
        $response->assertViewHas('sections');
        $response->assertViewHas('subjects');
        $response->assertViewHas('teachers');
        $response->assertViewHas('rooms');
        $response->assertViewHas('days');
    }

    /** @test */
    public function admin_can_create_schedule_period(): void
    {
        $admin = $this->adminUser();
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $subject = $this->makeSubject($section);
        $room = $this->makeRoom();

        $payload = $this->payload($section, $subject, $teacher, $room);

        $response = $this->actingAs($admin)->post('/admin/schedule', $payload);

        $response->assertRedirect('/admin/schedule');
        $response->assertSessionHas('success', 'Schedule period created successfully.');

        $this->assertDatabaseHas('schedules', [
            'section_id'  => $section->section_id,
            'subject_id'  => $subject->subject_id,
            'teacher_id'  => $teacher->teacher_id,
            'room_id'     => $room->room_id,
            'day_of_week' => 'Monday',
            'start_time'  => '08:00',
            'end_time'    => '09:00',
        ]);
    }

    /** @test */
    public function admin_cannot_create_schedule_with_teacher_conflict(): void
    {
        $admin = $this->adminUser();
        [$user, $teacher] = $this->makeTeacher();
        $section1 = $this->makeSection();
        $section2 = $this->makeSection();
        $subject = $this->makeSubject($section1);
        $room = $this->makeRoom();

        // Create first schedule
        Schedule::create($this->payload($section1, $subject, $teacher, $room));

        // Try to create overlapping schedule for same teacher
        $payload = $this->payload($section2, $subject, $teacher, $room);

        $response = $this->actingAs($admin)->post('/admin/schedule', $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('conflict');
        $this->assertStringContainsString('Teacher is already scheduled', session('errors')->first('conflict'));

        // Should only have one schedule
        $this->assertEquals(1, Schedule::count());
    }

    /** @test */
    public function admin_cannot_create_schedule_with_room_conflict(): void
    {
        $admin = $this->adminUser();
        [$user1, $teacher1] = $this->makeTeacher();
        [$user2, $teacher2] = $this->makeTeacher();
        $section = $this->makeSection();
        $subject = $this->makeSubject($section);
        $room = $this->makeRoom();

        // Create first schedule
        Schedule::create($this->payload($section, $subject, $teacher1, $room));

        // Try to create overlapping schedule for same room, different teacher
        $payload = $this->payload($section, $subject, $teacher2, $room);

        $response = $this->actingAs($admin)->post('/admin/schedule', $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('conflict');
        $this->assertStringContainsString('Room is already booked', session('errors')->first('conflict'));

        $this->assertEquals(1, Schedule::count());
    }

    /** @test */
    public function admin_can_update_schedule_period(): void
    {
        $admin = $this->adminUser();
        [$user, $teacher] = $this->makeTeacher();
        $section = $this->makeSection();
        $subject = $this->makeSubject($section);
        $room = $this->makeRoom();

        $schedule = Schedule::create($this->payload($section, $subject, $teacher, $room));

        [$user2, $teacher2] = $this->makeTeacher();
        $room2 = $this->makeRoom();

        $updatedPayload = $this->payload($section, $subject, $teacher2, $room2);
        $updatedPayload['day_of_week'] = 'Tuesday';
        $updatedPayload['start_time'] = '10:00';
        $updatedPayload['end_time'] = '11:00';

        $response = $this->actingAs($admin)->put("/admin/schedule/{$schedule->schedule_id}", $updatedPayload);

        $response->assertRedirect('/admin/schedule');
        $response->assertSessionHas('success', 'Schedule period updated successfully.');

        $this->assertDatabaseHas('schedules', [
            'schedule_id' => $schedule->schedule_id,
            'teacher_id'  => $teacher2->teacher_id,
            'room_id'     => $room2->room_id,
            'day_of_week' => 'Tuesday',
            'start_time'  => '10:00',
            'end_time'    => '11:00',
        ]);
    }

    /** @test */
    public function admin_cannot_update_schedule_with_teacher_conflict(): void
    {
        $admin = $this->adminUser();
        [$user, $teacher1] = $this->makeTeacher();
        [$user2, $teacher2] = $this->makeTeacher();
        $section = $this->makeSection();
        $subject = $this->makeSubject($section);
        $room = $this->makeRoom();

        // Create existing schedule for teacher2 on Monday 08:00-09:00
        Schedule::create($this->payload($section, $subject, $teacher2, $room));

        // Create schedule for teacher1 that we'll try to update to conflict
        $schedule = Schedule::create([
            'section_id'  => $section->section_id,
            'subject_id'  => $subject->subject_id,
            'teacher_id'  => $teacher1->teacher_id,
            'room_id'     => $room->room_id,
            'day_of_week' => 'Tuesday',
            'start_time'  => '10:00:00',
            'end_time'    => '11:00:00',
        ]);

        // Try to update to Monday 08:00-09:00 (conflicts with teacher2)
        $payload = $this->payload($section, $subject, $teacher1, $room);

        $response = $this->actingAs($admin)->put("/admin/schedule/{$schedule->schedule_id}", $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('conflict');
        $this->assertStringContainsString('Room is already booked', session('errors')->first('conflict'));
    }

     /** @test */
     public function admin_can_delete_schedule_without_linked_assignments_or_quizzes(): void
     {
         $admin = $this->adminUser();
         [$user, $teacher] = $this->makeTeacher();
         $section = $this->makeSection();
         $subject = $this->makeSubject($section);
         $room = $this->makeRoom();

         $schedule = Schedule::create($this->payload($section, $subject, $teacher, $room));

         $response = $this->actingAs($admin)->delete("/admin/schedule/{$schedule->schedule_id}");

         $response->assertRedirect('/admin/schedule');
         $response->assertSessionHas('success', 'Schedule period deleted.');

         $this->assertDatabaseMissing('schedules', ['schedule_id' => $schedule->schedule_id]);
     }

     /** @test */
     public function admin_cannot_delete_schedule_with_linked_assignments(): void
     {
         $admin = $this->adminUser();
         [$user, $teacher] = $this->makeTeacher();
         $section = $this->makeSection();
         $subject = $this->makeSubject($section);
         $room = $this->makeRoom();

         $schedule = Schedule::create($this->payload($section, $subject, $teacher, $room));

         // Create linked assignment
         Assignment::create([
             'schedule_id' => $schedule->schedule_id,
             'title'       => 'Test Assignment',
             'description' => 'Test',
             'due_date'    => now()->addDays(7),
             'max_points'  => 100,
             'max_score'   => 100,
         ]);

         $response = $this->actingAs($admin)->delete("/admin/schedule/{$schedule->schedule_id}");

         $response->assertRedirect();
         $response->assertSessionHasErrors('conflict');
         $this->assertStringContainsString('linked assignment', session('errors')->first('conflict'));

         $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id]);
     }

     /** @test */
     public function admin_cannot_delete_schedule_with_linked_quizzes(): void
     {
         $admin = $this->adminUser();
         [$user, $teacher] = $this->makeTeacher();
         $section = $this->makeSection();
         $subject = $this->makeSubject($section);
         $room = $this->makeRoom();

         $schedule = Schedule::create($this->payload($section, $subject, $teacher, $room));

         // Create linked quiz
         \App\Models\Quiz::create([
             'schedule_id'      => $schedule->schedule_id,
             'title'            => 'Test Quiz',
             'description'      => 'Test',
             'time_limit_minutes' => 30,
             'start_time'       => now()->addHour(),
             'end_time'         => now()->addHours(2),
         ]);

         $response = $this->actingAs($admin)->delete("/admin/schedule/{$schedule->schedule_id}");

         $response->assertRedirect();
         $response->assertSessionHasErrors('conflict');
         $this->assertStringContainsString('linked assignment', session('errors')->first('conflict'));

         $this->assertDatabaseHas('schedules', ['schedule_id' => $schedule->schedule_id]);
     }

     /** @test */
     public function non_admin_role_is_forbidden_from_admin_schedule(): void
     {
         $teacherUser = User::create([
             'first_name' => 'Test', 'last_name' => 'Teacher', 'email' => uniqid() . '@example.com',
             'password' => Hash::make('password'), 'must_change_password' => false,
             'role' => 'Teacher', 'status' => 'Active',
         ]);

          $this->actingAs($teacherUser)->get("/admin/schedule")->assertForbidden();
         $this->actingAs($teacherUser)->post('/admin/schedule', [])->assertForbidden();
         $this->actingAs($teacherUser)->put('/admin/schedule/1', [])->assertForbidden();
         $this->actingAs($teacherUser)->delete('/admin/schedule/1')->assertForbidden();
     }

     /** @test */
     public function admin_schedule_validation_requires_all_fields(): void
     {
         $admin = $this->adminUser();

         $response = $this->actingAs($admin)->post('/admin/schedule', []);

         $response->assertSessionHasErrors([
             'section_id', 'subject_id', 'teacher_id', 'day_of_week', 'start_time', 'end_time',
         ]);
     }

     /** @test */
     public function admin_schedule_validation_enforces_time_order(): void
     {
         $admin = $this->adminUser();
         [$user, $teacher] = $this->makeTeacher();
         $section = $this->makeSection();
         $subject = $this->makeSubject($section);
         $room = $this->makeRoom();

         $payload = $this->payload($section, $subject, $teacher, $room);
         $payload['start_time'] = '10:00';
         $payload['end_time'] = '09:00'; // Before start_time

         $response = $this->actingAs($admin)->post('/admin/schedule', $payload);

         $response->assertSessionHasErrors('end_time');
     }

     /** @test */
     public function admin_schedule_no_conflict_when_updating_same_record(): void
     {
         $admin = $this->adminUser();
         [$user, $teacher] = $this->makeTeacher();
         $section = $this->makeSection();
         $subject = $this->makeSubject($section);
         $room = $this->makeRoom();

         $schedule = Schedule::create($this->payload($section, $subject, $teacher, $room));

         // Update with same time (should not conflict with itself)
         $payload = $this->payload($section, $subject, $teacher, $room);
         $payload['subject_id'] = $subject->subject_id; // Just changing something trivial

         $response = $this->actingAs($admin)->put("/admin/schedule/{$schedule->schedule_id}", $payload);

         $response->assertRedirect('/admin/schedule');
         $response->assertSessionHas('success');
     }
}