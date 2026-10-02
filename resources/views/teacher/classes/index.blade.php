{{-- Teacher: My Classes (TeacherClassroomController@classes) — one card per schedule the teacher handles --}}
@extends('layouts.teacher')
@section('title', 'My Classes')

{{--
    ponytail: this view used to double as the old TeacherClassController's student
    roster/"Add Student" page (table + modal, posting to teacher.classes.store).
    That whole UI depended on $schoolYears/$allStudents/$sections/$students/$stats,
    none of which TeacherClassroomController::classes() passes, so it crashed with
    "Undefined variable" before ever reaching the section below. Removed rather than
    rewired — "Add Student" needs its own page/modal here if that feature comes back.
--}}
@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $timeRange = fn ($s) => \Carbon\Carbon::parse($s->start_time)->format('g:i A') . ' – ' . \Carbon\Carbon::parse($s->end_time)->format('g:i A');
    $chip = 'inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-ink/70 ring-1 ring-ink/10 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10';
    $quickLink = 'flex flex-col items-center gap-1 rounded-xl border border-[#e3e8f4] bg-white px-2 py-2.5 text-center text-xs font-semibold text-ink/75 transition hover:border-[#c9d6f5] hover:bg-[#f5f7fd] hover:text-brand-deep dark:border-slate-700 dark:bg-slate-900/45 dark:text-slate-300 dark:hover:border-blue-400/40 dark:hover:bg-blue-500/10 dark:hover:text-white';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
@endphp

@section('content')
<div class="bento-content"
     x-data="{
        formOpen: false,
        mode: 'instant',
        form: { schedule_id: null, title: '', meeting_link: '', start_datetime: '', end_datetime: '' },
        openMeet(mode, scheduleId, subjectName) {
            this.mode = mode;
            const start = new Date();
            const end = new Date(start.getTime() + 60 * 60000);
            const toLocal = (d) => d.toISOString().slice(0, 16);
            this.form = {
                schedule_id: scheduleId,
                title: subjectName + (mode === 'instant' ? ' — Live Meeting' : ' — Meeting'),
                meeting_link: '',
                start_datetime: toLocal(start),
                end_datetime: toLocal(end),
            };
            this.formOpen = true;
        },
     }">
    <div class="bento-header">
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Classes</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Every class you handle, with quick links to its work, grades and attendance.</p>
    </div>

    <div class="bento-grid">
        @forelse ($classes as $class)
            @php
                $s = $class->schedule;
                $name = $s->subject?->subject_name ?? 'Subject';
                $initials = collect(explode(' ', $name))->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->join('');
                $q = ['schedule_id' => $s->schedule_id];
            @endphp
            <article class="bento-card" style="--thumb-hue: {{ crc32($name) % 360 }}">
                <div class="flex items-start gap-3">
                    <div class="bento-card__thumb !mb-0">{{ Str::upper($initials) }}</div>
                    <div class="min-w-0">
                        <div class="bento-card__label">{{ $s->section?->strand?->strand_code ?? 'Class' }} · Grade {{ $s->section?->grade_level }}</div>
                        <h2 class="bento-card__title truncate">{{ $name }}</h2>
                        <p class="bento-card__description">Section {{ $s->section?->section_name ?? '—' }}</p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="{{ $chip }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ Str::substr($s->day_of_week, 0, 3) }} · {{ $timeRange($s) }}
                    </span>
                    <span class="{{ $chip }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657 13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0ZM15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        {{ $s->room?->room_name ?? 'No room' }}
                    </span>
                    <span class="{{ $chip }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        {{ $class->students }} {{ Str::plural('student', $class->students) }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <a href="{{ route('teacher.assignments.index', $q) }}" class="{{ $quickLink }}">
                        <span class="font-display text-lg font-bold text-ink dark:text-white">{{ $class->assignments }}</span> Assignments
                    </a>
                    <a href="{{ route('teacher.quizzes.index', $q) }}" class="{{ $quickLink }}">
                        <span class="font-display text-lg font-bold text-ink dark:text-white">{{ $class->quizzes }}</span> Quizzes
                    </a>
                    <a href="{{ route('teacher.materials.index', $q) }}" class="{{ $quickLink }}">
                        <span class="font-display text-lg font-bold text-ink dark:text-white">{{ $class->materials }}</span> Materials
                    </a>
                </div>

                <div class="mt-3 flex gap-2 pt-1">
                    <a href="{{ route('teacher.grades.index', $q) }}" class="st-btn-outline btn-sm flex-1">Gradebook</a>
                    <a href="{{ route('teacher.attendance.index', $q) }}" class="btn-navy btn-sm flex-1">Take attendance</a>
                </div>

                {{-- Google Meet: Start Now / Join Live, Schedule, or End — one active meeting per class at a time. --}}
                <div class="mt-2 flex gap-2">
                    @if ($class->meeting)
                        @if ($class->meeting->isLive())
                            <a href="{{ $class->meeting->meeting_link }}" target="_blank" rel="noopener" class="flex-1 rounded-lg bg-emerald-600 px-3 py-2 text-center text-xs font-semibold text-white transition hover:bg-emerald-700">Join Live Meeting</a>
                            <form method="POST" action="{{ route('teacher.meetings.end', $class->meeting->event_id) }}">
                                @csrf @method('PUT')
                                <button type="submit" class="rounded-lg border border-ink/10 px-3 py-2 text-xs font-semibold text-ink/70 transition hover:bg-ink/5 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">End</button>
                            </form>
                        @else
                            <span class="flex-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                Meeting scheduled {{ $class->meeting->start_datetime->format('M j, g:i A') }}
                            </span>
                            <form method="POST" action="{{ route('teacher.meetings.destroy', $class->meeting->event_id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-lg border border-ink/10 px-3 py-2 text-xs font-semibold text-ink/70 transition hover:bg-ink/5 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">Cancel</button>
                            </form>
                        @endif
                    @else
                        <button type="button" @click="openMeet('schedule', {{ $s->schedule_id }}, @js($name))" class="st-btn-outline btn-sm flex-1">Schedule Meet</button>
                        <button type="button" @click="openMeet('instant', {{ $s->schedule_id }}, @js($name))" class="btn-navy btn-sm flex-1">Start Meet Now</button>
                    @endif
                </div>
            </article>
        @empty
            <article class="bento-card bento-card--full items-center !py-10 text-center">
                <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg></span>
                <div class="bento-card__label mt-3">Classes</div>
                <h2 class="bento-card__title">No classes assigned yet</h2>
                <p class="bento-card__description">Your classes appear here once the admin adds you to a class schedule.</p>
            </article>
        @endforelse
    </div>

    {{-- Start/Schedule Meet modal --}}
    <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div x-show="formOpen" x-transition.opacity @click="formOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="formOpen" x-transition method="POST" :action="mode === 'instant' ? @js(route('teacher.meetings.instant')) : @js(route('teacher.meetings.store'))"
              class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-[#e3e8f4] bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            @csrf
            <input type="hidden" name="schedule_id" :value="form.schedule_id">
            <div class="flex items-start justify-between gap-4 border-b border-[#eef1f8] px-6 py-4 dark:border-slate-700">
                <h2 class="text-lg font-bold text-ink dark:text-white" x-text="mode === 'instant' ? 'Start Meet Now' : 'Schedule a Meet'"></h2>
                <button type="button" @click="formOpen = false" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-ink/40 transition hover:bg-ink/5 dark:hover:bg-white/5">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5">
                <p class="text-xs text-ink/55 dark:text-slate-400">Create the meeting on <a href="https://meet.google.com/new" target="_blank" rel="noopener" class="underline">meet.google.com</a>, then paste the link below.</p>
                <div>
                    <label class="{{ $label }}">Google Meet link <span class="text-red-500">*</span></label>
                    <input type="url" name="meeting_link" x-model="form.meeting_link" required placeholder="https://meet.google.com/abc-defg-hij" class="{{ $input }}">
                </div>
                <template x-if="mode === 'schedule'">
                    <div>
                        <div>
                            <label class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="form.title" required maxlength="150" class="{{ $input }}">
                        </div>
                        <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}">Starts <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="start_datetime" x-model="form.start_datetime" required class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Ends <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="end_datetime" x-model="form.end_datetime" required class="{{ $input }}">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-[#eef1f8] bg-white/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <button type="button" @click="formOpen = false" class="st-btn-outline">Cancel</button>
                <button type="submit" class="btn-navy" x-text="mode === 'instant' ? 'Start Meeting' : 'Schedule Meeting'"></button>
            </div>
        </form>
    </div>
</div>
@endsection
