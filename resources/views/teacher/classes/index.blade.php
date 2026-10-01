{{-- Teacher: Class Management --}}
@extends('layouts.teacher')
@section('title', 'My Classes')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';

    $studentName = fn ($student) => $student?->user
        ? trim($student->user->last_name . ', ' . $student->user->first_name)
        : ($student?->student_number ?? 'Unknown student');

    $sectionLabel = fn ($section) => $section
        ? trim('Grade ' . $section->grade_level . ' - ' . $section->section_name)
        : '—';

    $semesters = ['1st Semester', '2nd Semester'];
    $activeYear = $schoolYears->firstWhere('status', 'active');
    $blankForm = ['student_id' => '', 'section_id' => '', 'school_year_id' => (string) ($activeYear?->school_year_id ?? ''), 'semester' => '1st Semester'];
    $reopen = $errors->any() && old('_form') === 'add_student';
    $initialForm = $reopen ? array_merge($blankForm, array_intersect_key(old(), $blankForm)) : $blankForm;

    // Client-side searchable combobox options for the student field, so teachers
    // can type a name/LRN instead of scrolling a <select> with the whole roster.
    $studentOptions = $allStudents->map(fn ($student) => [
        'id' => (string) $student->student_id,
        'label' => $studentName($student) . ' (' . $student->student_number . ')',
    ])->values();
    $initialStudent = $studentOptions->firstWhere('id', $initialForm['student_id']);
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8" x-data="{ addOpen: @js($reopen) }" @keydown.escape.window="addOpen = false">
    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Classes</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your assigned sections and the students enrolled in each.</p>
        </div>
        <button type="button" @click="addOpen = true" class="btn-navy inline-flex items-center gap-2 self-start sm:self-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Add Student
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['sections'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sections</p>
        </div>
        <div class="{{ $card }} px-5 py-4">
            <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $stats['students'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Students</p>
        </div>
    </div>

    <section class="{{ $card }}">
        {{-- Filters --}}
        <form method="GET" action="{{ route('teacher.classes.index') }}" class="grid grid-cols-1 gap-3 border-b border-blue-100 p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-700">
            <div class="relative sm:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, student no. or LRN..." class="{{ $input }} pl-9">
            </div>
            <select name="section_id" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All sections</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->section_id }}" @selected((string) $selectedSectionId === (string) $section->section_id)>
                        {{ $sectionLabel($section) }}
                    </option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <select name="grade_level" class="{{ $input }}" onchange="this.form.submit()">
                    <option value="">All grade levels</option>
                    @foreach (['11', '12'] as $gradeLevel)
                        <option value="{{ $gradeLevel }}" @selected($selectedGradeLevel === $gradeLevel)>Grade {{ $gradeLevel }}</option>
                    @endforeach
                </select>
                @if (request()->hasAny(['search', 'section_id', 'grade_level']))
                    <a href="{{ route('teacher.classes.index') }}" title="Clear filters"
                       class="flex shrink-0 items-center justify-center rounded-lg border border-slate-300 px-3 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-600 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
        {{-- Students table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                        <th class="px-5 py-3">Student</th>
                        <th class="px-5 py-3">LRN</th>
                        <th class="px-5 py-3">Section</th>
                        <th class="px-5 py-3">Strand</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($students as $enrollment)
                        @php $student = $enrollment->student; @endphp
                        <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-emerald-500 to-emerald-700 text-[11px] font-bold text-white">
                                        {{ mb_strtoupper(mb_substr($student?->user?->first_name ?? 'S', 0, 1) . mb_substr($student?->user?->last_name ?? '', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="font-medium text-slate-800 dark:text-slate-100">{{ $studentName($student) }}</p>
                                        <p class="font-mono text-xs text-slate-500 dark:text-slate-400">{{ $student?->student_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $student?->lrn }}</td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $sectionLabel($enrollment->section) }}</td>
                            <td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $enrollment->section?->strand?->strand_code ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ $enrollment->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No students found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="border-t border-blue-100 px-5 py-4 dark:border-slate-700">
                {{ $students->links() }}
            </div>
        @endif
    </section>

    {{-- Add Student modal --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="add-student-title">
        <div x-show="addOpen" x-transition.opacity @click="addOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="addOpen"
              x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
              method="POST" action="{{ route('teacher.classes.store') }}"
              class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            <input type="hidden" name="_form" value="add_student">

            <div class="flex items-start justify-between gap-4 border-b border-blue-100 px-6 py-4 dark:border-slate-700">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Add Student</p>
                    <h2 id="add-student-title" class="text-lg font-bold text-slate-900 dark:text-white">Enroll into one of your sections</h2>
                </div>
                <button type="button" @click="addOpen = false" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5 sm:grid-cols-2">
                <div class="sm:col-span-2 relative"
                     x-data="{
                        options: @js($studentOptions),
                        query: @js($initialStudent['label'] ?? ''),
                        selectedId: @js($initialForm['student_id']),
                        studentOpen: false,
                        get filtered() {
                            const q = this.query.trim().toLowerCase();
                            if (!q) return this.options.slice(0, 50);
                            // ponytail: plain substring match over the already-loaded list, not
                            // fuzzy search. Upgrade to a server-side search endpoint if the
                            // roster grows large enough that shipping it all client-side hurts.
                            return this.options.filter(o => o.label.toLowerCase().includes(q)).slice(0, 50);
                        },
                        select(option) { this.selectedId = option.id; this.query = option.label; this.studentOpen = false; },
                     }"
                     @click.outside="studentOpen = false">
                    <label for="student_search" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Student <span class="text-red-500">*</span></label>
                    <input type="hidden" name="student_id" :value="selectedId">
                    <input type="text" id="student_search" autocomplete="off"
                           x-model="query" @focus="studentOpen = true" @input="studentOpen = true; selectedId = ''"
                           placeholder="Type a name or LRN&hellip;" class="{{ $input }}">
                    <ul x-show="studentOpen && filtered.length" x-cloak
                        class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg dark:border-slate-600 dark:bg-slate-800">
                        <template x-for="option in filtered" :key="option.id">
                            <li @click="select(option)" x-text="option.label"
                                class="cursor-pointer px-3 py-2 text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-slate-700"></li>
                        </template>
                    </ul>
                    <p x-show="studentOpen && query.trim() && !filtered.length" x-cloak class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 shadow-lg dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400">No matching students.</p>
                    @error('student_id') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="add_section_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Section <span class="text-red-500">*</span></label>
                    <select id="add_section_id" name="section_id" required class="{{ $input }}">
                        <option value="">Select a section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}" @selected((string) old('section_id', $initialForm['section_id']) === (string) $section->section_id)>
                                {{ $sectionLabel($section) }} ({{ $section->students_count }}/{{ $section->max_slots }} enrolled)
                            </option>
                        @endforeach
                    </select>
                    @error('section_id') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="add_school_year_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">School Year <span class="text-red-500">*</span></label>
                    <select id="add_school_year_id" name="school_year_id" required class="{{ $input }}">
                        <option value="">Select a school year</option>
                        @foreach ($schoolYears as $year)
                            <option value="{{ $year->school_year_id }}" @selected((string) old('school_year_id', $initialForm['school_year_id']) === (string) $year->school_year_id)>
                                {{ $year->year }}{{ $year->status === 'active' ? ' (active)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_year_id') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="add_semester" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Semester <span class="text-red-500">*</span></label>
                    <select id="add_semester" name="semester" required class="{{ $input }}">
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester }}" @selected(old('semester', $initialForm['semester']) === $semester)>{{ $semester }}</option>
                        @endforeach
                    </select>
                    @error('semester') <p class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <button type="button" @click="addOpen = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="btn-navy">Enroll Student</button>
            </div>
        </form>
    </div>
</div>
@endsection
