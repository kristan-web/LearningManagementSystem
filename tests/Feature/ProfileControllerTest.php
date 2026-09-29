<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): array
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'role' => 'Student',
            'status' => 'Active',
        ]);

        $student = Student::create([
            'user_id' => $user->user_id,
            'lrn' => '123456789012',
            'student_number' => 'S-0001',
            'grade_level' => '11',
        ]);

        return [$user, $student];
    }

    private function makeTeacher(): array
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'Teacher',
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'role' => 'Teacher',
            'status' => 'Active',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->user_id,
            'teacher_number' => 'T-0001',
            'specialization' => 'Mathematics',
        ]);

        return [$user, $teacher];
    }

    public function test_student_can_view_own_profile(): void
    {
        [$user] = $this->makeStudent();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('My Profile');
        $response->assertSee('123456789012');
    }

    public function test_teacher_can_view_own_profile(): void
    {
        [$user] = $this->makeTeacher();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('T-0001');
    }

    public function test_student_can_update_editable_fields(): void
    {
        [$user] = $this->makeStudent();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'contact_number' => '09171234567',
            'address' => '123 Rizal St.',
            'birthdate' => '2008-05-10',
            'gender' => 'Male',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('09171234567', $user->contact_number);
        $this->assertSame('123 Rizal St.', $user->address);
        $this->assertSame('Male', $user->gender);
    }

    public function test_student_cannot_tamper_with_locked_identity_fields(): void
    {
        [$user, $student] = $this->makeStudent();

        $this->actingAs($user)->put(route('profile.update'), [
            'first_name' => 'Hacked',
            'last_name' => 'Name',
            'email' => 'hacked@example.com',
            'role' => 'Admin',
            'status' => 'Suspended',
        ]);

        $user->refresh();
        $student->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('Student', $user->last_name);
        $this->assertSame('student@example.com', $user->email);
        $this->assertSame('Student', $user->role);
        $this->assertSame('Active', $user->status);
        $this->assertSame('123456789012', $student->lrn);
        $this->assertSame('S-0001', $student->student_number);
    }

    public function test_teacher_can_update_specialization(): void
    {
        [$user, $teacher] = $this->makeTeacher();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'specialization' => 'Physics',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $teacher->refresh();
        $this->assertSame('Physics', $teacher->specialization);
    }

    public function test_teacher_cannot_tamper_with_teacher_number(): void
    {
        [$user, $teacher] = $this->makeTeacher();

        $this->actingAs($user)->put(route('profile.update'), [
            'teacher_number' => 'T-9999',
        ]);

        $teacher->refresh();
        $this->assertSame('T-0001', $teacher->teacher_number);
    }

    public function test_admin_cannot_access_teacher_student_profile_route(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'must_change_password' => false,
            'role' => 'Admin',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $response->assertForbidden();
    }

    public function test_password_can_be_updated_and_is_hashed(): void
    {
        [$user] = $this->makeStudent();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-password', $user->password));
    }
}
