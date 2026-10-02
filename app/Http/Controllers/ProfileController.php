<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Show the signed-in Teacher/Student their own profile (name, email, IDs are read-only). */
    public function edit(Request $request): View
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['Teacher', 'Student'], true), 403);

        $teacher = $user->role === 'Teacher' ? Teacher::where('user_id', $user->user_id)->first() : null;
        $student = $user->role === 'Student' ? Student::where('user_id', $user->user_id)->first() : null;

        $view = $user->role === 'Student' ? 'student.profile.edit' : 'profile.edit';

        return view($view, compact('user', 'teacher', 'student'));
    }

    /** Update contact/personal details only — name, email, role, status and IDs can't be changed here. */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['Teacher', 'Student'], true), 403);

        $validated = $request->validate([
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'password' => 'nullable|string|min:8|confirmed',
            'specialization' => 'nullable|string|max:255',
        ]);

        $user->update([
            'contact_number' => $validated['contact_number'] ?? null,
            'address' => $validated['address'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'password' => ($validated['password'] ?? null) ? Hash::make($validated['password']) : $user->password,
        ]);

        if ($user->role === 'Teacher') {
            Teacher::where('user_id', $user->user_id)->update([
                'specialization' => $validated['specialization'] ?? '',
            ]);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
