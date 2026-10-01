<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\AccountController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\User;

class WebAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        if (! $request->filled('identifier') && $request->filled('email')) {
            $request->merge(['identifier' => $request->input('email')]);
        }

        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::query()
            ->where(function ($query) use ($credentials) {
                $query->where('email', $credentials['identifier']);
            })
            ->where('status', 'Active')
            ->where('is_deleted', false)
            ->first();

        $storedHash = trim((string) $user?->password);

        // Ensure the stored password is a valid Bcrypt hash before checking
        if (! $user || ! $this->isValidBcrypt($storedHash) || ! Hash::check($credentials['password'], $storedHash)) {
            return back()->withErrors([
                'identifier' => 'The provided credentials are incorrect.',
            ])->onlyInput('identifier');
        }

        // Auto-rehash if password hash is outdated
        if (Hash::needsRehash($storedHash)) {
            $user->update(['password' => $credentials['password']]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Every teacher page loads the teacher profile with firstOrFail(), so a Teacher account with no
        // `teachers` row (role switched outside the admin form) got a 404 on /teacher. Create it here;
        // the placeholder number is unique per user and the admin can replace it in Edit Account.
        if ($user->role === 'Teacher') {
            \App\Models\Teacher::firstOrCreate(
                ['user_id' => $user->user_id],
                ['teacher_number' => 'TCH-PENDING-' . $user->user_id, 'specialization' => ''],
            );
        }

        if ($user->must_change_password && in_array($user->role, ['Teacher', 'Student'], true)) {
            return redirect()->route('password.change');
        }

        return match ($user->role) {
            'Admin', 'Staff', 'Registrar', 'Accounting' => redirect()->intended('/admin/'),
            'Teacher' => redirect()->intended('/teacher/'),
            'Student' => redirect()->intended('/student/'),
            default => redirect()->intended('/login'),
        };
    }

    /**
     * Check if a string is a valid Bcrypt hash.
     */
    private function isValidBcrypt(?string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        $info = password_get_info(trim($password));

        return ($info['algoName'] ?? '') === 'bcrypt';
    }

    public function adminDashboard(Request $request): View|RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $latestUsers = User::query()
            ->orderByDesc('created_at')
            ->orderByDesc('user_id')
            ->limit(10)
            ->get();

        // Chart data for dashboard
        $usersByRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->orderByDesc('total')
            ->get();

        $attendanceByStatus = DB::table('attendance_records')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $studentsByGrade = DB::table('students')
            ->select('grade_level', DB::raw('count(*) as total'))
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // Students by created_at month and grade_level for line/bar chart
        $studentsByCreatedAt = DB::table('students')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                'grade_level',
                DB::raw('count(*) as total')
            )
            ->groupBy('period', 'grade_level')
            ->orderBy('period')
            ->orderBy('grade_level')
            ->get();

        $studentsByStatus = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.user_id')
            ->select('users.status', DB::raw('count(*) as total'))
            ->groupBy('users.status')
            ->orderBy('users.status')
            ->get();

        $studentsByGender = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.user_id')
            ->select('users.gender', DB::raw('count(*) as total'))
            ->groupBy('users.gender')
            ->orderBy('users.gender')
            ->get();

        $enrollmentBySemester = DB::table('enrollments')
            ->select('semester', DB::raw('count(*) as total'))
            ->groupBy('semester')
            ->orderBy('semester')
            ->get();

        $enrollmentByStatus = DB::table('enrollments')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $enrollmentTrends = DB::table('enrollments')
            ->select('school_year', DB::raw('count(*) as total'))
            ->groupBy('school_year')
            ->orderBy('school_year')
            ->get();

        $teachersBySpecialization = DB::table('teachers')
            ->select('specialization', DB::raw('count(*) as total'))
            ->whereNotNull('specialization')
            ->groupBy('specialization')
            ->orderBy('specialization')
            ->get();

        $subjectCount = DB::table('subjects')->count();
        $subjectsByType = DB::table('subjects')
            ->select('subject_type', DB::raw('count(*) as total'))
            ->groupBy('subject_type')
            ->orderBy('subject_type')
            ->get();

        return view('admin.dashboard', compact(
            'latestUsers',
            'usersByRole',
            'attendanceByStatus',
            'studentsByGrade',
            'studentsByStatus',
            'studentsByGender',
            'studentsByCreatedAt',
            'enrollmentBySemester',
            'enrollmentByStatus',
            'enrollmentTrends',
            'teachersBySpecialization',
            'subjectCount',
            'subjectsByType'
        ));
    }

    public function adminUsers(Request $request): View|RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by is_deleted
        if ($request->filled('deleted') && $request->deleted === '1') {
            $query->where('is_deleted', true);
        } elseif ($request->filled('deleted') && $request->deleted === '0') {
            $query->where('is_deleted', false);
        }

        $users = $query->orderByDesc('created_at')
                       ->orderByDesc('user_id')
                       ->paginate(10)
                       ->withQueryString();

        // Chart data: users by role (separate query)
        $roleQuery = User::query();
        if ($request->filled('search')) {
            $roleQuery->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $roleQuery->where('role', $request->role);
        }
        if ($request->filled('deleted') && $request->deleted === '1') {
            $roleQuery->where('is_deleted', true);
        } elseif ($request->filled('deleted') && $request->deleted === '0') {
            $roleQuery->where('is_deleted', false);
        }
        $usersByRole = $roleQuery->selectRaw('role, COUNT(*) as total')
                                  ->groupBy('role')
                                  ->get();

        // Chart data: users by status (separate query)
        $statusQuery = User::query();
        if ($request->filled('search')) {
            $statusQuery->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $statusQuery->where('role', $request->role);
        }
        if ($request->filled('deleted') && $request->deleted === '1') {
            $statusQuery->where('is_deleted', true);
        } elseif ($request->filled('deleted') && $request->deleted === '0') {
            $statusQuery->where('is_deleted', false);
        }
        $usersByStatus = $statusQuery->selectRaw('status, COUNT(*) as total')
                                      ->groupBy('status')
                                      ->get();

        return view('admin.users.index', compact('users', 'usersByRole', 'usersByStatus'));
    }

    public function adminUsersCreate(): View|RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        return view('admin.users.create');
    }

    public function adminUsersStore(Request $request): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,Staff,Registrar,Accounting,Teacher,Student',
            'status' => 'required|in:Active,Inactive,Suspended,Locked',
            'is_deleted' => 'boolean',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            // Student fields
            'student_lrn' => 'nullable|string|max:12',
            'student_number' => 'nullable|string|max:20',
            'grade_level' => 'nullable|in:11,12',
            'strand_id' => 'nullable|integer',
            'student_guardian_id' => 'nullable|integer|exists:guardians,guardian_id',
            // Teacher fields
            'teacher_number' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                // Teacher/Student accounts must change their admin-assigned password on first login.
                'must_change_password' => in_array($validated['role'], ['Teacher', 'Student'], true),
                'role' => $validated['role'],
                'status' => $validated['status'],
                'is_deleted' => $validated['is_deleted'] ?? false,
                'contact_number' => $validated['contact_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'gender' => $validated['gender'] ?? null,
            ]);

            if ($validated['role'] === 'Teacher') {
                $teacherData = [
                    'user_id' => $user->user_id,
                    'teacher_number' => $validated['teacher_number'] ?? '',
                    'specialization' => $validated['specialization'] ?? '',
                ];
                \App\Models\Teacher::create($teacherData);
            } elseif ($validated['role'] === 'Student') {
                $studentData = [
                    'user_id' => $user->user_id,
                    'lrn' => $validated['student_lrn'] ?? '',
                    'student_number' => $validated['student_number'] ?? '',
                    'grade_level' => $validated['grade_level'] ?? '11',
                ];
                if ($validated['student_guardian_id']) {
                    $studentData['guardian_id'] = $validated['student_guardian_id'];
                }
                \App\Models\Student::create($studentData);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Account created successfully.');
    }

    public function adminUsersShow($user_id): View|RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $user = User::with(['teacher', 'student'])->findOrFail($user_id);

        return view('admin.users.show', compact('user'));
    }

    public function adminUsersEdit(Request $request, $user_id): View|RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $user = User::findOrFail($user_id);
        $teacher = $user->role === 'Teacher' ? \App\Models\Teacher::where('user_id', $user_id)->first() : null;
        $student = $user->role === 'Student' ? \App\Models\Student::where('user_id', $user_id)->first() : null;

        return view('admin.users.edit', compact('user', 'teacher', 'student'));
    }

    public function adminUsersUpdate(Request $request, $user_id): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $user = User::findOrFail($user_id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'email' => 'required|string|email|max:100|unique:users,email,' . $user_id . ',user_id',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:Admin,Staff,Registrar,Accounting,Teacher,Student',
            'status' => 'required|in:Active,Inactive,Suspended,Locked',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            // Student fields
            'student_lrn' => 'nullable|string|max:12',
            'student_number' => 'nullable|string|max:20',
            'grade_level' => 'nullable|in:11,12',
            'student_guardian_id' => 'nullable|integer|exists:guardians,guardian_id',
            // Teacher fields
            'teacher_number' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($user, $validated) {
            $passwordChanged = (bool) $validated['password'];

            $user->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'email' => $validated['email'],
                'password' => $passwordChanged ? Hash::make($validated['password']) : $user->password,
                // Only re-flag for a forced change when the admin actually assigned a new password.
                'must_change_password' => $passwordChanged
                    ? in_array($validated['role'], ['Teacher', 'Student'], true)
                    : $user->must_change_password,
                'role' => $validated['role'],
                'status' => $validated['status'],
                'contact_number' => $validated['contact_number'] ?? null,
                'address' => $validated['address'] ?? null,
                'birthdate' => $validated['birthdate'] ?? null,
                'gender' => $validated['gender'] ?? null,
            ]);

            // Handle role change / teacher/student data
            $existingTeacher = \App\Models\Teacher::where('user_id', $user->user_id)->first();
            $existingStudent = \App\Models\Student::where('user_id', $user->user_id)->first();

            if ($validated['role'] === 'Teacher') {
                if ($existingTeacher) {
                    $existingTeacher->update([
                        'teacher_number' => $validated['teacher_number'] ?? '',
                        'specialization' => $validated['specialization'] ?? '',
                    ]);
                } else {
                    \App\Models\Teacher::create([
                        'user_id' => $user->user_id,
                        'teacher_number' => $validated['teacher_number'] ?? '',
                        'specialization' => $validated['specialization'] ?? '',
                    ]);
                }
                if ($existingStudent) {
                    $existingStudent->delete();
                }
            } elseif ($validated['role'] === 'Student') {
                if ($existingStudent) {
                    $existingStudent->update([
                        'lrn' => $validated['student_lrn'] ?? '',
                        'student_number' => $validated['student_number'] ?? '',
                        'grade_level' => $validated['grade_level'] ?? '11',
                        'guardian_id' => $validated['student_guardian_id'] ?? null,
                    ]);
                } else {
                    \App\Models\Student::create([
                        'user_id' => $user->user_id,
                        'lrn' => $validated['student_lrn'] ?? '',
                        'student_number' => $validated['student_number'] ?? '',
                        'grade_level' => $validated['grade_level'] ?? '11',
                        'guardian_id' => $validated['student_guardian_id'] ?? null,
                    ]);
                }
                if ($existingTeacher) {
                    $existingTeacher->delete();
                }
            } else {
                if ($existingTeacher) $existingTeacher->delete();
                if ($existingStudent) $existingStudent->delete();
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'Account updated successfully.');
    }

    /** Email the acting admin a code that must be entered to delete this user. */
    public function adminUsersDeleteOtp($user_id): JsonResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $user = User::findOrFail($user_id);
        $key = 'delete-otp:' . auth()->id() . ":{$user->user_id}";

        // Brevo takes a few minutes to deliver; reopening the modal must not replace a code that is still on its way.
        if (($pending = Cache::get($key)) && ($pending['sent_at'] ?? 0) > now()->subMinutes(3)->timestamp) {
            return response()->json(['message' => 'A code was already sent to ' . auth()->user()->email . '. It can take a few minutes to arrive.']);
        }

        $code = (string) random_int(100000, 999999);
        Cache::put($key, ['hash' => Hash::make($code), 'attempts' => 0, 'sent_at' => now()->timestamp], now()->addMinutes(10));

        $intro = 'Enter this code to confirm deleting the account of ' . trim($user->first_name . ' ' . $user->last_name) . '.';
        if (! AccountController::mailOtp(auth()->user()->email, $code, $intro)) {
            Cache::forget($key);

            return response()->json(['message' => 'We could not send the code right now. Please try again later.'], 502);
        }

        return response()->json(['message' => 'Code sent to ' . auth()->user()->email . '.']);
    }

    public function adminUsersDelete(Request $request, $user_id): JsonResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $code = $request->validate(['code' => ['required', 'digits:6']])['code'];
        $user = User::findOrFail($user_id);

        if ($error = AccountController::checkOtp('delete-otp:' . auth()->id() . ":{$user->user_id}", $code)) {
            return response()->json(['message' => $error], 422);
        }

        $user->update(['is_deleted' => true]);
        session()->flash('success', 'Account deleted successfully.');

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    public function adminUsersRestore($user_id): RedirectResponse
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Staff', 'Registrar', 'Accounting'], true), 403);

        $user = User::findOrFail($user_id);
        $user->update(['is_deleted' => false]);

        return redirect()->back()->with('success', 'Account restored successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
