{{-- Profile: Teacher/Student self-service edit (mirrors admin/settings UI; identity fields are locked) --}}
@extends(auth()->user()->role === 'Teacher' ? 'layouts.teacher' : 'layouts.student')
@section('title', 'My Profile')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $cardHead = 'flex items-start gap-3 border-b border-blue-100 px-6 py-4 dark:border-slate-700';
    $cardIcon = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300';
    $cardTitle = 'text-base font-semibold text-slate-900 dark:text-white';
    $cardSub = 'mt-0.5 text-xs text-slate-500 dark:text-slate-400';
    $label = 'mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-xs transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $readonly = 'block w-full cursor-not-allowed rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-400';
    $hint = 'mt-1 text-xs text-slate-500 dark:text-slate-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $dashboardRoute = $user->role === 'Teacher' ? 'teacher.dashboard' : 'student.dashboard';
    $accentText = $user->role === 'Teacher' ? 'text-emerald-700 dark:text-emerald-300' : 'text-blue-700 dark:text-blue-300';
    $accentBg = $user->role === 'Teacher' ? 'bg-emerald-100 dark:bg-emerald-500/15' : 'bg-blue-100 dark:bg-blue-500/15';
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">

    {{-- Page header --}}
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Profile</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">View your account details and update your contact information and password.</p>
    </div>

    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>Please fix the highlighted fields below.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf
        @method('PUT')

        {{-- Left column: account overview --}}
        <div class="space-y-6 lg:col-span-1">
            <section class="{{ $card }} overflow-hidden">
                <div class="h-20 bg-linear-to-r from-sky-400 via-blue-500 to-indigo-500"></div>
                <div class="-mt-10 flex flex-col items-center px-6 pb-6 text-center">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-linear-to-br from-sky-400 to-blue-600 text-2xl font-bold text-white ring-4 ring-white dark:ring-slate-800">
                        {{ mb_strtoupper(mb_substr($user->first_name ?? '', 0, 1) . mb_substr($user->last_name ?? '', 0, 1)) }}
                    </span>
                    <h2 class="mt-3 text-lg font-semibold text-slate-900 dark:text-white">{{ trim($user->first_name . ' ' . $user->last_name) }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <span class="rounded-full {{ $accentBg }} px-3 py-1 text-xs font-semibold {{ $accentText }}">{{ $user->role }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $user->status === 'Active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">{{ $user->status }}</span>
                    </div>
                </div>
            </section>

            {{-- Identity (read-only — name, email, role, status and school-issued IDs can't be self-edited) --}}
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">Identity</h3>
                        <p class="{{ $cardSub }}">Managed by the school administrator. Read-only.</p>
                    </div>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <div>
                        <label for="full_name" class="{{ $label }}">Full Name</label>
                        <input type="text" id="full_name" value="{{ trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name) }}" class="{{ $readonly }}" readonly disabled>
                    </div>
                    <div>
                        <label for="email_ro" class="{{ $label }}">Email Address</label>
                        <input type="text" id="email_ro" value="{{ $user->email }}" class="{{ $readonly }}" readonly disabled>
                    </div>
                    @if ($user->role === 'Student')
                        <div>
                            <label for="lrn" class="{{ $label }}">LRN</label>
                            <input type="text" id="lrn" value="{{ $student->lrn ?? 'N/A' }}" class="{{ $readonly }}" readonly disabled>
                        </div>
                        <div>
                            <label for="student_number" class="{{ $label }}">Student Number</label>
                            <input type="text" id="student_number" value="{{ $student->student_number ?? 'N/A' }}" class="{{ $readonly }}" readonly disabled>
                        </div>
                        <div>
                            <label for="grade_level" class="{{ $label }}">Grade Level</label>
                            <input type="text" id="grade_level" value="{{ $student->grade_level ?? 'N/A' }}" class="{{ $readonly }}" readonly disabled>
                        </div>
                    @elseif ($user->role === 'Teacher')
                        <div>
                            <label for="teacher_number" class="{{ $label }}">Teacher Number</label>
                            <input type="text" id="teacher_number" value="{{ $teacher->teacher_number ?? 'N/A' }}" class="{{ $readonly }}" readonly disabled>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- Right column: editable cards --}}
        <div class="space-y-6 lg:col-span-2">

            {{-- Personal information (only birthdate/gender are editable; name is locked) --}}
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">Personal Information</h3>
                        <p class="{{ $cardSub }}">Your birthdate and gender.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div>
                        <label for="birthdate" class="{{ $label }}">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}" class="{{ $input }}">
                        @error('birthdate') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <span class="{{ $label }}">Gender</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['Male', 'Female', 'Other'] as $gender)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gender" value="{{ $gender }}" class="peer sr-only" {{ old('gender', $user->gender) === $gender ? 'checked' : '' }}>
                                    <span class="flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:border-blue-300 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-focus-visible:ring-3 peer-focus-visible:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-500/15 dark:peer-checked:text-blue-300">{{ $gender }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('gender') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    @if ($user->role === 'Teacher')
                        <div class="sm:col-span-2">
                            <label for="specialization" class="{{ $label }}">Specialization</label>
                            <input type="text" id="specialization" name="specialization" maxlength="255" value="{{ old('specialization', $teacher->specialization ?? '') }}" class="{{ $input }}" placeholder="e.g. Mathematics">
                            @error('specialization') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            </section>

            {{-- Contact information --}}
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">Contact Information</h3>
                        <p class="{{ $cardSub }}">How the school can reach you.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div>
                        <label for="contact_number" class="{{ $label }}">Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number" maxlength="20" value="{{ old('contact_number', $user->contact_number) }}" class="{{ $input }}" placeholder="09XX XXX XXXX">
                        @error('contact_number') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="{{ $label }}">Address</label>
                        <textarea id="address" name="address" rows="2" maxlength="255" class="{{ $input }} resize-none" placeholder="Street, Barangay, City, Province">{{ old('address', $user->address) }}</textarea>
                        <p class="{{ $hint }}">Up to 255 characters.</p>
                        @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Security --}}
            <section class="{{ $card }}" x-data="{ show: false }">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">Security</h3>
                        <p class="{{ $cardSub }}">Leave blank to keep your current password.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="{{ $label }}">New Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" type="password" id="password" name="password" minlength="8" autocomplete="new-password" class="{{ $input }} pr-11" placeholder="At least 8 characters">
                            <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'"
                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 transition hover:text-blue-600 dark:hover:text-blue-300">
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 0 1 1.563-3.029m5.858.908a3 3 0 1 1 4.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532 3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0 1 12 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 0 1-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="{{ $label }}">Confirm New Password</label>
                        <input :type="show ? 'text' : 'password'" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" class="{{ $input }}" placeholder="Repeat new password">
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route($dashboardRoute) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:-translate-y-0.5 hover:shadow-blue-500/40 focus:outline-none focus-visible:ring-3 focus-visible:ring-blue-500/30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

