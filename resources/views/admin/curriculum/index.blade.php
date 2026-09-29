{{-- Admin: Curriculum & Subject Management --}}
@extends('layouts.admin')
@section('title', 'Curriculum Management')

@php
    $subjectTypes = ['Core', 'Applied', 'Specialized'];
    $gradeLevels = ['11', '12'];
    $semesters = ['1st Semester', '2nd Semester'];
    $statuses = ['Active', 'Inactive'];

    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $typeBadge = [
        'Core' => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
        'Applied' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Specialized' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
    ];

    $blankForm = [
        'strand_id' => '', 'teacher_id' => '', 'subject_code' => '', 'subject_name' => '', 'subject_type' => 'Core',
        'grade_level' => '11', 'semester' => '1st Semester', 'units' => '', 'description' => '', 'status' => 'Active',
    ];
    // Re-open the modal with the submitted values when validation fails
    $reopen = $errors->any() && old('_form') === 'subject';
    $initialForm = $reopen ? array_merge($blankForm, array_intersect_key(old(), $blankForm)) : $blankForm;
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8"
     x-data="{
        formOpen: @js($reopen),
        mode: @js($reopen ? old('_mode', 'create') : 'create'),
        editId: @js($reopen ? old('_subject_id') : null),
        form: @js($initialForm),
        blank: @js($blankForm),
        deleteOpen: false,
        deleteTarget: { id: null, code: '', name: '' },
        storeUrl: @js(route('admin.subjects.store')),
        updateUrl: @js(route('admin.subjects.update', '__ID__')),
        destroyUrl: @js(route('admin.subjects.destroy', '__ID__')),
        openCreate() { this.mode = 'create'; this.editId = null; this.form = { ...this.blank }; this.formOpen = true; },
        openEdit(subject) {
           this.mode = 'edit';
           this.editId = subject.subject_id;
           this.form = { ...this.blank, ...subject, strand_id: String(subject.strand_id ?? ''), teacher_id: String(subject.teacher_id ?? ''), description: subject.description ?? '' };
           this.formOpen = true;
        },
        openDelete(subject) { this.deleteTarget = { id: subject.subject_id, code: subject.subject_code, name: subject.subject_name }; this.deleteOpen = true; },
        get formAction() { return this.mode === 'edit' ? this.updateUrl.replace('__ID__', this.editId) : this.storeUrl; }
     }"
     @keydown.escape.window="formOpen = false; deleteOpen = false">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ink dark:text-white">Curriculum & Subjects</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Create, update and organize subjects per strand, grade level and semester.</p>
        </div>
        <button type="button" @click="openCreate()"
                class="btn-navy">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Add Subject
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['Total Subjects', $stats['total'], 'text-ink dark:text-white'],
            ['Active', $stats['active'], 'text-emerald-600 dark:text-emerald-400'],
            ['Core', $stats['core'], 'text-brand dark:text-blue-400'],
            ['Applied / Specialized', $stats['applied'] . ' / ' . $stats['specialized'], 'text-violet-600 dark:text-violet-400'],
        ] as [$statLabel, $statValue, $statColor])
            <div class="{{ $card }} px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink/60 dark:text-slate-400">{{ $statLabel }}</p>
                <p class="mt-1 text-2xl font-bold {{ $statColor }}">{{ $statValue }}</p>
            </div>
        @endforeach
    </div>

    {{-- Subjects table --}}
    <section class="{{ $card }}">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.curriculum.index') }}" class="grid grid-cols-1 gap-3 border-b border-blue-100 p-4 sm:grid-cols-2 lg:grid-cols-7 dark:border-slate-700">
            <div class="relative sm:col-span-2">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or name..." class="{{ $input }} pl-9">
            </div>
            <select name="strand_id" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All strands</option>
                @foreach ($strands as $strand)
                    <option value="{{ $strand->strand_id }}" @selected((string) request('strand_id') === (string) $strand->strand_id)>{{ $strand->strand_code }}</option>
                @endforeach
            </select>
            <select name="teacher_id" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All teachers</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->teacher_id }}" @selected((string) request('teacher_id') === (string) $teacher->teacher_id)>{{ $teacher->user ? $teacher->user->last_name . ', ' . $teacher->user->first_name : $teacher->teacher_number }}</option>
                @endforeach
            </select>
            <select name="subject_type" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach ($subjectTypes as $type)
                    <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <select name="grade_level" class="{{ $input }}" onchange="this.form.submit()">
                <option value="">All grades</option>
                @foreach ($gradeLevels as $grade)
                    <option value="{{ $grade }}" @selected(request('grade_level') === $grade)>Grade {{ $grade }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <select name="status" class="{{ $input }}" onchange="this.form.submit()">
                    <option value="">Any status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                @if (request()->hasAny(['search', 'strand_id', 'teacher_id', 'subject_type', 'grade_level', 'status']))
                    <a href="{{ route('admin.curriculum.index') }}" title="Clear filters"
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
                        <th class="px-5 py-3">Subject</th>
                        <th class="hidden px-5 py-3 md:table-cell">Strand</th>
                        <th class="hidden px-5 py-3 xl:table-cell">Teacher</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="hidden px-5 py-3 lg:table-cell">Grade & Semester</th>
                        <th class="hidden px-5 py-3 sm:table-cell">Units</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-100/70 dark:divide-slate-700/70">
                    @forelse ($subjects as $subject)
                        @php
                            $payload = $subject->only(['subject_id', 'strand_id', 'teacher_id', 'subject_code', 'subject_name', 'subject_type', 'grade_level', 'semester', 'units', 'description', 'status']);
                        @endphp
                        <tr class="transition-colors hover:bg-blue-50/60 dark:hover:bg-blue-500/5">
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-ink dark:text-white">{{ $subject->subject_name }}</p>
                                <p class="font-mono text-xs text-brand dark:text-blue-400">{{ $subject->subject_code }}</p>
                            </td>
                            <td class="hidden px-5 py-3.5 text-ink/70 md:table-cell dark:text-slate-300">
                                {{ $subject->strand?->strand_code ?? 'All strands' }}
                            </td>
                            <td class="hidden px-5 py-3.5 xl:table-cell">
                                @if ($subject->teacher)
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-[#3a52a0] to-ink text-[10px] font-bold text-white">
                                            {{ mb_strtoupper(mb_substr($subject->teacher->user?->first_name ?? 'T', 0, 1) . mb_substr($subject->teacher->user?->last_name ?? '', 0, 1)) }}
                                        </span>
                                        <span class="text-ink/80 dark:text-slate-200">{{ $subject->teacher->user ? trim($subject->teacher->user->first_name . ' ' . $subject->teacher->user->last_name) : $subject->teacher->teacher_number }}</span>
                                    </div>
                                @else
                                    <span class="text-xs italic text-ink/45">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeBadge[$subject->subject_type] ?? '' }}">{{ $subject->subject_type }}</span>
                            </td>
                            <td class="hidden px-5 py-3.5 text-ink/70 lg:table-cell dark:text-slate-300">
                                Grade {{ $subject->grade_level }} <span class="text-ink/45">·</span> {{ $subject->semester }}
                            </td>
                            <td class="hidden px-5 py-3.5 text-ink/70 sm:table-cell dark:text-slate-300">{{ number_format((float) $subject->units, 1) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $subject->status === 'Active' ? 'text-emerald-600 dark:text-emerald-400' : 'text-ink/45' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $subject->status === 'Active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $subject->status }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-1.5">
                                    <button type="button" @click="openEdit(@js($payload))" title="Edit" aria-label="Edit {{ $subject->subject_name }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand transition hover:scale-105 hover:bg-ink hover:text-white dark:text-blue-300">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>
                                    <button type="button" @click="openDelete(@js($payload))" title="Delete" aria-label="Delete {{ $subject->subject_name }}"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-500/10 text-red-600 transition hover:scale-105 hover:bg-red-500 hover:text-white dark:text-red-300">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                                <p class="mt-2 text-sm font-medium text-ink/70 dark:text-slate-300">No subjects found</p>
                                <p class="text-xs text-ink/45">Add a subject or change the filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($subjects->hasPages())
            <div class="border-t border-blue-100 px-5 py-4 dark:border-slate-700">
                {{ $subjects->links() }}
            </div>
        @endif
    </section>

    {{-- Create / Edit modal --}}
    <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="subject-form-title">
        <div x-show="formOpen" x-transition.opacity @click="formOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="formOpen"
              x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
              method="POST" :action="formAction"
              class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="subject">
            <input type="hidden" name="_mode" :value="mode">
            <input type="hidden" name="_subject_id" :value="editId">

            <div class="flex items-start justify-between gap-4 border-b border-blue-100 px-6 py-4 dark:border-slate-700">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-brand dark:text-blue-400" x-text="mode === 'edit' ? 'Edit Subject' : 'New Subject'"></p>
                    <h2 id="subject-form-title" class="text-lg font-bold text-ink dark:text-white" x-text="mode === 'edit' ? form.subject_name : 'Add a subject'"></h2>
                </div>
                <button type="button" @click="formOpen = false" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-ink/45 transition hover:bg-slate-100 hover:text-ink dark:hover:bg-slate-700 dark:hover:text-white">&times;</button>
            </div>

            <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5 sm:grid-cols-2">
                <div>
                    <label for="subject_code" class="{{ $label }}">Subject Code <span class="text-red-500">*</span></label>
                    <input type="text" id="subject_code" name="subject_code" x-model="form.subject_code" maxlength="20" required class="{{ $input }} font-mono uppercase" placeholder="e.g. CORE-101">
                    @error('subject_code') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="strand_id" class="{{ $label }}">Strand</label>
                    <select id="strand_id" name="strand_id" x-model="form.strand_id" class="{{ $input }}">
                        <option value="">All strands (shared subject)</option>
                        @foreach ($strands as $strand)
                            <option value="{{ $strand->strand_id }}">{{ $strand->strand_code }} — {{ $strand->strand_name }}</option>
                        @endforeach
                    </select>
                    @error('strand_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="subject_name" class="{{ $label }}">Subject Name <span class="text-red-500">*</span></label>
                    <input type="text" id="subject_name" name="subject_name" x-model="form.subject_name" maxlength="150" required class="{{ $input }}" placeholder="e.g. Oral Communication in Context">
                    @error('subject_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="teacher_id" class="{{ $label }}">Assigned Teacher</label>
                    <select id="teacher_id" name="teacher_id" x-model="form.teacher_id" class="{{ $input }}">
                        <option value="">Unassigned</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->teacher_id }}">{{ $teacher->user ? $teacher->user->last_name . ', ' . $teacher->user->first_name : 'Teacher' }} ({{ $teacher->teacher_number }})</option>
                        @endforeach
                    </select>
                    @error('teacher_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <span class="{{ $label }}">Subject Type <span class="text-red-500">*</span></span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($subjectTypes as $type)
                            <label class="cursor-pointer">
                                <input type="radio" name="subject_type" value="{{ $type }}" x-model="form.subject_type" class="peer sr-only">
                                <span class="flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-ink/70 transition hover:border-brand/40 peer-checked:border-brand peer-checked:bg-blue-50 peer-checked:text-brand-deep peer-focus-visible:ring-3 peer-focus-visible:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-500/15 dark:peer-checked:text-blue-300">{{ $type }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('subject_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="grade_level" class="{{ $label }}">Grade Level <span class="text-red-500">*</span></label>
                    <select id="grade_level" name="grade_level" x-model="form.grade_level" required class="{{ $input }}">
                        @foreach ($gradeLevels as $grade)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endforeach
                    </select>
                    @error('grade_level') <p class="{{ $error }}">{{ $message }}</p> @enderror
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
                <div>
                    <label for="units" class="{{ $label }}">Units <span class="text-red-500">*</span></label>
                    <input type="number" id="units" name="units" x-model="form.units" step="0.1" min="0" max="99.9" required class="{{ $input }}" placeholder="e.g. 3.0">
                    @error('units') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="{{ $label }}">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" x-model="form.status" required class="{{ $input }}">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="{{ $label }}">Description</label>
                    <textarea id="description" name="description" x-model="form.description" rows="3" class="{{ $input }} resize-none" placeholder="Short summary of the subject (optional)"></textarea>
                    @error('description') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <button type="button" @click="formOpen = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-ink/80 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="btn-navy" x-text="mode === 'edit' ? 'Save Changes' : 'Create Subject'"></button>
            </div>
        </form>
    </div>

    {{-- Delete confirmation modal --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="subject-delete-title">
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
                    <h2 id="subject-delete-title" class="text-lg font-bold text-ink dark:text-white">Delete subject?</h2>
                    <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">
                        <span class="font-semibold text-ink/80 dark:text-slate-200" x-text="deleteTarget.code + ' — ' + deleteTarget.name"></span>
                        will be removed permanently. This cannot be undone.
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
