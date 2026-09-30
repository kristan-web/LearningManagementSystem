{{-- Admin: Class Schedule Management --}}
@extends('layouts.admin')
@section('title', 'Class Schedule')

@php
    $card  = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $reopen      = $errors->any() && old('_form') === 'schedule';
    $blankForm   = [
        'section_id'  => '', 'subject_id' => '', 'teacher_id' => '',
        'room_id'     => '', 'day_of_week' => '', 'start_time' => '', 'end_time' => '',
    ];
    $initialForm = $reopen ? array_merge($blankForm, array_intersect_key(old(), $blankForm)) : $blankForm;

    $teacherName = fn (App\Models\Teacher $t) => trim(($t->user?->last_name ?? '') . ', ' . ($t->user?->first_name ?? '')) ?: "Teacher #{$t->teacher_id}";
    $timeRange   = fn (App\Models\Schedule $s) => \Carbon\Carbon::parse($s->start_time)->format('h:i A') . ' – ' . \Carbon\Carbon::parse($s->end_time)->format('h:i A');
@endphp

@section('content')
<div class="mx-auto w-full max-w-7xl space-y-6 pt-8"
     x-data="{
        formOpen: @js($reopen),
        mode: @js($reopen ? old('_mode', 'create') : 'create'),
        editId: @js($reopen ? old('_schedule_id') : null),
        form: @js($initialForm),
        blank: @js($blankForm),
        deleteOpen: false,
        deleteTarget: { id: null, label: '' },
        conflictError: @js($errors->first('conflict')),
        storeUrl: @js(route('admin.schedule.store')),
        updateUrl: @js(route('admin.schedule.update', '__ID__')),
        destroyUrl: @js(route('admin.schedule.destroy', '__ID__')),
        openCreate() { this.mode = 'create'; this.editId = null; this.form = { ...this.blank }; this.conflictError = null; this.formOpen = true; },
        openEdit(s) {
            this.mode = 'edit'; this.editId = s.schedule_id;
            this.form = { section_id: String(s.section_id), subject_id: String(s.subject_id), teacher_id: String(s.teacher_id), room_id: s.room_id ? String(s.room_id) : '', day_of_week: s.day_of_week, start_time: s.start_time, end_time: s.end_time };
            this.conflictError = null; this.formOpen = true;
        },
        openDelete(s) { this.deleteTarget = { id: s.schedule_id, label: `${s.section?.section_name ?? 'Section'} - ${s.subject?.subject_name ?? 'Subject'} (${s.day_of_week} ${s.start_time} - ${s.end_time})` }; this.deleteOpen = true; },
        get formAction() { return this.mode === 'edit' ? this.updateUrl.replace('__ID__', this.editId) : this.storeUrl; }
     }"
     @keydown.escape.window="formOpen = false; deleteOpen = false">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ink dark:text-white">Class Schedule</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Manage timetable periods for all sections. Conflicts are detected automatically.</p>
        </div>
        <button type="button" @click="openCreate()" class="btn-navy inline-flex items-center gap-2 self-start sm:self-auto">
            <svg class="h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5v14"/>
            </svg>
            Add Period
        </button>
    </div>

    {{-- Flash / conflict messages --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->has('conflict') && !$reopen)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            {{ $errors->first('conflict') }}
        </div>
    @endif

    {{-- Schedule table --}}
    <div class="{{ $card }} overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    <th class="px-4 py-3">Section</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">Teacher</th>
                    <th class="px-4 py-3">Day</th>
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">Room</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($schedules as $period)
                    @php
                        $scheduleJs = [
                            'schedule_id' => $period->schedule_id,
                            'section_id'  => $period->section_id,
                            'subject_id'  => $period->subject_id,
                            'teacher_id'  => $period->teacher_id,
                            'room_id'     => $period->room_id,
                            'day_of_week' => $period->day_of_week,
                            'start_time'  => \Carbon\Carbon::parse($period->start_time)->format('H:i'),
                            'end_time'    => \Carbon\Carbon::parse($period->end_time)->format('H:i'),
                            'label'       => ($period->subject?->subject_name ?? '?') . ' — ' . ($period->section?->section_name ?? '?'),
                        ];
                    @endphp
                    <tr class="align-middle hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">
                            {{ $period->section?->section_name ?? '—' }}
                            <span class="ml-1 text-xs text-slate-400">Gr.{{ $period->section?->grade_level }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $period->subject?->subject_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $teacherName($period->teacher) }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $period->day_of_week }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $timeRange($period) }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $period->room?->room_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <button type="button" @click="openEdit(@js($scheduleJs))"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-brand hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10">Edit</button>
                                <button type="button" @click="openDelete(@js($scheduleJs))"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            No schedule periods yet. Click <strong>Add Period</strong> to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>


    {{-- Create / Edit modal --}}
    <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center"
         role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title">
        <div x-show="formOpen" x-transition.opacity @click="formOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="formOpen" x-transition method="POST" :action="formAction"
              class="relative w-full max-w-xl rounded-2xl border border-blue-100 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            <input type="hidden" name="_method" x-bind:value="mode === 'edit' ? 'PUT' : 'POST'">
            <input type="hidden" name="_form" value="schedule">
            <input type="hidden" name="_mode" x-bind:value="mode">
            <input type="hidden" name="_schedule_id" x-bind:value="editId">
            <div class="border-b border-blue-100 px-6 py-4 dark:border-slate-700">
                <h2 id="schedule-modal-title" class="text-lg font-bold text-ink dark:text-white"
                    x-text="mode === 'edit' ? 'Edit Schedule Period' : 'Add Schedule Period'"></h2>
            </div>
            <div class="space-y-4 px-6 py-5">
                <div x-show="conflictError" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-xs font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300" x-text="conflictError"></div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}" for="sched-section">Section</label>
                        <select id="sched-section" name="section_id" x-model="form.section_id" class="{{ $input }}">
                            <option value="">— select section —</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->section_id }}">Gr.{{ $section->grade_level }} – {{ $section->section_name }}</option>
                            @endforeach
                        </select>
                        @error('section_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="sched-subject">Subject</label>
                        <select id="sched-subject" name="subject_id" x-model="form.subject_id" class="{{ $input }}">
                            <option value="">— select subject —</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->subject_id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="sched-teacher">Teacher</label>
                        <select id="sched-teacher" name="teacher_id" x-model="form.teacher_id" class="{{ $input }}">
                            <option value="">— select teacher —</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->teacher_id }}">{{ $teacherName($teacher) }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="sched-room">Room <span class="text-slate-400">(optional)</span></label>
                        <select id="sched-room" name="room_id" x-model="form.room_id" class="{{ $input }}">
                            <option value="">— no room —</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->room_id }}">{{ $room->room_name }}@if($room->building) · {{ $room->building }}@endif</option>
                            @endforeach
                        </select>
                        @error('room_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}" for="sched-day">Day of Week</label>
                        <select id="sched-day" name="day_of_week" x-model="form.day_of_week" class="{{ $input }}">
                            <option value="">— select day —</option>
                            @foreach ($days as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('day_of_week') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}" for="sched-start">Start</label>
                            <input type="time" id="sched-start" name="start_time" x-model="form.start_time" class="{{ $input }}">
                            @error('start_time') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $label }}" for="sched-end">End</label>
                            <input type="time" id="sched-end" name="end_time" x-model="form.end_time" class="{{ $input }}">
                            @error('end_time') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <button type="button" @click="formOpen = false"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-ink/80 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="btn-navy" x-text="mode === 'edit' ? 'Save Changes' : 'Add Period'"></button>
            </div>
        </form>
    </div>

    {{-- Delete confirmation modal --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         role="dialog" aria-modal="true" aria-labelledby="schedule-delete-title">
        <div x-show="deleteOpen" x-transition.opacity @click="deleteOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="deleteOpen" x-transition method="POST" :action="destroyUrl.replace('__ID__', deleteTarget.id)"
              class="relative w-full max-w-md rounded-2xl border border-blue-100 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            @method('DELETE')
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </span>
                <div>
                    <h2 id="schedule-delete-title" class="text-lg font-bold text-ink dark:text-white">Delete this period?</h2>
                    <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">
                        <span class="font-semibold text-ink/80 dark:text-slate-200" x-text="deleteTarget.label"></span>
                        will be removed permanently.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="deleteOpen = false"
                        class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-ink/80 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">Cancel</button>
                <button type="submit" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">Delete</button>
            </div>
        </form>
    </div>

</div>
@endsection

