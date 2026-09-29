{{-- Admin: Enrollment Records --}}
@extends('layouts.admin')
@section('title', 'Enrollment Records')

@php
    $semesters = ['1st Semester', '2nd Semester'];
    $statuses = ['Enrolled', 'Pending', 'Dropped'];

    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $statusBadge = [
        'Enrolled' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Dropped' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
    ];

    $activeYear = $schoolYears->firstWhere('status', 'active');
    $blankForm = [
        'student_id' => '', 'section_id' => '', 'school_year_id' => (string) ($activeYear?->school_year_id ?? ''),
        'semester' => '1st Semester', 'status' => 'Pending', 'student_name' => '',
    ];
    // Re-open the modal with the submitted values when validation fails
    $reopen = $errors->any() && old('_form') === 'enrollment';
    $initialForm = $reopen ? array_merge($blankForm, array_intersect_key(old(), $blankForm)) : $blankForm;

    $studentName = fn ($student) => $student?->user
        ? trim($student->user->last_name . ', ' . $student->user->first_name)
        : ($student?->student_number ?? 'Unknown student');
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8"
     x-data="{
        formOpen: @js($reopen),
        mode: @js($reopen ? old('_mode', 'create') : 'create'),
        editId: @js($reopen ? old('_enrollment_id') : null),
        form: @js($initialForm),
        blank: @js($blankForm),
        deleteOpen: false,
        deleteTarget: { id: null, name: '' },
        storeUrl: @js(route('admin.enrollment.store')),
        updateUrl: @js(route('admin.enrollment.update', '__ID__')),
        destroyUrl: @js(route('admin.enrollment.destroy', '__ID__')),
        openCreate() { this.mode = 'create'; this.editId = null; this.form = { ...this.blank }; this.formOpen = true; },
        openEdit(enrollment) {
           this.mode = 'edit';
           this.editId = enrollment.enrollment_id;
           this.form = { ...this.blank, ...enrollment, student_id: String(enrollment.student_id), section_id: String(enrollment.section_id), school_year_id: String(enrollment.school_year_id) };
           this.formOpen = true;
        },
        openDelete(enrollment) { this.deleteTarget = { id: enrollment.enrollment_id, name: enrollment.student_name }; this.deleteOpen = true; },
        get formAction() { return this.mode === 'edit' ? this.updateUrl.replace('__ID__', this.editId) : this.storeUrl; }
     }"
     @keydown.escape.window="formOpen = false; deleteOpen = false">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ink dark:text-white">Enrollment Records</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Enroll students into sections and track their status per school year and semester.</p>
        </div>
        <button type="button" @click="openCreate()"
                class="btn-navy">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Enroll Student
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Total Records', $stats['total'], 'text-ink dark:text-white'],
            ['Enrolled', $stats['enrolled'], 'text-emerald-600 dark:text-emerald-400'],
            ['Pending', $stats['pending'], 'text-amber-600 dark:text-amber-400'],
            ['Dropped', $stats['dropped'], 'text-red-600 dark:text-red-400'],
        ] as [$statLabel, $statValue, $statColor])
            <div class="{{ $card }} px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink/60 dark:text-slate-400">{{ $statLabel }}</p>
                <p class="mt-1 text-2xl font-bold {{ $statColor }}">{{ $statValue }}</p>
            </div>
        @endforeach
    </div>

    {{-- Enrollments table --}}
    <section class="{{ $card }}">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.enrollment.index') }}" class="grid grid-cols-1 gap-3 border-b border-blue-100 p-4 sm:grid-cols-2 lg:grid-cols-6 dark:border-slate-700">
            <div class="relative sm:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, student no. or LRN..." class="{{ $input }} pl-9">
            </div>
            <select name="school_year_id" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All school years</option>
                @foreach ($schoolYears as $year)
                    <option value="{{ $year->school_year_id }}" @selected((string) request('school_year_id') === (string) $year->school_year_id)>{{ $year->year }}</option>
                @endforeach
            </select>
            <select name="section_id" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All sections</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->section_id }}" @selected((string) request('section_id') === (string) $section->section_id)>G{{ $section->grade_level }} {{ $section->section_name }}</option>
                @endforeach
            </select>
            <select name="semester" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All semesters</option>
                @foreach ($semesters as $semester)
                    <option value="{{ $semester }}" @selected(request('semester') === $semester)>{{ $semester }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <select name="status" class="{{ $input }}" onchange="this.form.submit()">
                    <option value="">Any status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @if (request()->hasAny(['search', 'school_year_id', 'section_id', 'semester', 'status']))
                    <a href="{{ route('admin.enrollment.index') }}" title="Clear filters"
                       class="flex shrink-0 items-center justify-center rounded-lg border border-slate-300 px-3 text-ink/60 transition hover:bg-slate-50 hover:text-ink dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[11px] font-bold uppercase tracking-wider text-ink/60 dark:text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Student</th>
                        <th class="hidden px-5 py-3 md:table-cell">Section</th>
                        <th class="hidden px-5 py-3 lg:table-cell">School Year & Semester</th>
                        <th class="hidden px-5 py-3 sm:table-cell">Date Enrolled</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-100/70 dark:divide-slate-700/70">
                    @forelse ($enrollments as $enrollment)
                        @php
                            $student = $enrollment->student;
                            $payload = $enrollment->only(['enrollment_id', 'student_id', 'section_id', 'school_year_id', 'semester', 'status'])
                                + ['student_name' => $studentName($student)];
                        @endphp
                        <tr class="transition-colors hover:bg-blue-50/60 dark:hover:bg-blue-500/5">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-[#3a52a0] to-ink text-[11px] font-bold text-white">
                                        {{ mb_strtoupper(mb_substr($student?->user?->first_name ?? 'S', 0, 1) . mb_substr($student?->user?->last_name ?? '', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-semibold text-ink dark:text-white">{{ $studentName($student) }}</p>
                                        <p class="font-mono text-xs text-brand dark:text-blue-400">{{ $student?->student_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-5 py-3.5 text-ink/70 md:table-cell dark:text-slate-300">
                                @if ($enrollment->section)
                                    Grade {{ $enrollment->section->grade_level }} · {{ $enrollment->section->section_name }}
                                    <p class="text-xs text-ink/45">{{ $enrollment->section->strand?->strand_code }}</p>
                                @else
                                    <span class="text-xs italic text-ink/45">No section</span>
                                @endif
                            </td>
                            <td class="hidden px-5 py-3.5 text-ink/70 lg:table-cell dark:text-slate-300">
                                {{ $enrollment->school_year }} <span class="text-ink/45">·</span> {{ $enrollment->semester }}
                            </td>
                            <td class="hidden px-5 py-3.5 text-ink/70 sm:table-cell dark:text-slate-300">
                                {{ $enrollment->date_enrolled?->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge[$enrollment->status] ?? '' }}">{{ $enrollment->status }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" @click="openEdit(@js($payload))" title="Edit" aria-label="Edit enrollment of {{ $studentName($student) }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand transition hover:scale-105 hover:bg-ink hover:text-white dark:text-blue-300">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>
                                    <button type="button" @click="openDelete(@js($payload))" title="Delete" aria-label="Delete enrollment of {{ $studentName($student) }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-600 transition hover:scale-105 hover:bg-red-500 hover:text-white dark:text-red-300">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                <p class="mt-2 text-sm font-medium text-ink/70 dark:text-slate-300">No enrollment records found</p>
                                <p class="text-xs text-ink/45">Enroll a student or change the filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="border-t border-blue-100 px-5 py-4 dark:border-slate-700">
                {{ $enrollments->links() }}
            </div>
        @endif
    </section>

    {{-- Create / Edit modal --}}
    <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="enrollment-form-title">
        <div x-show="formOpen" x-transition.opacity @click="formOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="formOpen"
              x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
              method="POST" :action="formAction"
              class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="enrollment">
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="_enrollment_id" :value="editId">

            <div class="flex items-start justify-between gap-4 border-b border-blue-100 px-6 py-4 dark:border-slate-700">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand dark:text-blue-400" x-text="mode === 'edit' ? 'Edit Enrollment' : 'New Enrollment'"></p>
                    <h2 id="enrollment-form-title" class="text-lg font-bold text-ink dark:text-white" x-text="mode === 'edit' ? form.student_name : 'Enroll a student'"></h2>
                </div>
                <button type="button" @click="formOpen = false" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-ink/45 transition hover:bg-slate-100 hover:text-ink dark:hover:bg-slate-700 dark:hover:text-white">&times;</button>
            </div>

            <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="student_id" class="{{ $label }}">Student <span class="text-red-500">*</span></label>
                    <select id="student_id" name="student_id" x-model="form.student_id" required class="{{ $input }}">
                        <option value="">Select a student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->student_id }}">{{ $studentName($student) }} ({{ $student->student_number }}) · Grade {{ $student->grade_level }}</option>
                        @endforeach
                    </select>
                    @error('student_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="section_id" class="{{ $label }}">Section <span class="text-red-500">*</span></label>
                    <select id="section_id" name="section_id" x-model="form.section_id" required class="{{ $input }}">
                        <option value="">Select a section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}">Grade {{ $section->grade_level }} · {{ $section->section_name }} ({{ $section->strand?->strand_code }}) — {{ $section->enrolled_count }}/{{ $section->max_slots }} enrolled{{ $section->status !== 'Open' ? ' · ' . $section->status : '' }}</option>
                        @endforeach
                    </select>
                    @error('section_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="school_year_id" class="{{ $label }}">School Year <span class="text-red-500">*</span></label>
                    <select id="school_year_id" name="school_year_id" x-model="form.school_year_id" required class="{{ $input }}">
                        <option value="">Select a school year</option>
                        @foreach ($schoolYears as $year)
                            <option value="{{ $year->school_year_id }}">{{ $year->year }}{{ $year->status === 'active' ? ' (active)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('school_year_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="semester" class="{{ $label }}">Semester <span class="text-red-500">*</span></label>
                    <select id="semester" name="semester" x-model="form.semester" required class="{{ $input }}">
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester }}">{{ $semester }}</option>
                        @endforeach
                    </select>
                    @error('semester') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <span class="{{ $label }}">Status <span class="text-red-500">*</span></span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($statuses as $status)
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="{{ $status }}" x-model="form.status" class="peer sr-only">
                                <span class="flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-ink/70 transition hover:border-brand/40 peer-checked:border-brand peer-checked:bg-blue-50 peer-checked:text-brand-deep peer-focus-visible:ring-3 peer-focus-visible:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-500/15 dark:peer-checked:text-blue-300">{{ $status }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <button type="button" @click="formOpen = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-ink/80 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="btn-navy" x-text="mode === 'edit' ? 'Save Changes' : 'Enroll Student'"></button>
            </div>
        </form>
    </div>

    {{-- Delete confirmation modal --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="enrollment-delete-title">
        <div x-show="deleteOpen" x-transition.opacity @click="deleteOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="deleteOpen" x-transition method="POST" :action="destroyUrl.replace('__ID__', deleteTarget.id)"
              class="relative w-full max-w-md rounded-2xl border border-blue-100 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            @method('DELETE')
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </span>
                <div>
                    <h2 id="enrollment-delete-title" class="text-lg font-bold text-ink dark:text-white">Delete enrollment record?</h2>
                    <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">
                        The enrollment of <span class="font-semibold text-ink/80 dark:text-slate-200" x-text="deleteTarget.name"></span>
                        will be removed permanently. Set the status to Dropped to keep the record.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="deleteOpen = false" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-ink/80 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Cancel</button>
                <button type="submit" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection
