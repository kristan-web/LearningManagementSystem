{{-- Teacher: Attendance (TeacherClassroomController@attendance / saveAttendance) — mark a class roster for a date --}}
@extends('layouts.teacher')
@section('title', 'Attendance')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $classLabel = fn ($s) => trim(($s->subject?->subject_name ?? '') . ' — ' . ($s->section?->section_name ?? '') . ' (' . Str::substr($s->day_of_week, 0, 3) . ' ' . \Carbon\Carbon::parse($s->start_time)->format('g:i A') . ')');
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:focus:border-blue-400';
    $statusStyle = [
        'Present' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-500/15 dark:peer-checked:text-emerald-300',
        'Late' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:border-amber-400 dark:peer-checked:bg-amber-500/15 dark:peer-checked:text-amber-300',
        'Absent' => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:border-red-400 dark:peer-checked:bg-red-500/15 dark:peer-checked:text-red-300',
        'Excused' => 'peer-checked:border-brand peer-checked:bg-blue-50 peer-checked:text-brand-deep dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-500/15 dark:peer-checked:text-blue-300',
    ];
    $dot = ['Present' => 'bg-emerald-500', 'Late' => 'bg-amber-500', 'Absent' => 'bg-red-500', 'Excused' => 'bg-brand'];
    $dateValue = $date->toDateString();
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header">
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Attendance</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Pick a class and a date, then mark each student. Everyone starts as Present, so change only the exceptions.</p>
    </div>

    @if (! $selected)
        <div class="bento-grid">
            <article class="bento-card bento-card--full items-center !py-10 text-center">
                <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                <h2 class="bento-card__title mt-3">No classes yet</h2>
                <p class="bento-card__description">You can take attendance once you have a class schedule.</p>
            </article>
        </div>
    @else
        {{-- Class + date picker --}}
        <div class="mx-auto w-full max-w-6xl px-3 pt-3">
            <form method="GET" action="{{ route('teacher.attendance.index') }}" class="grid grid-cols-1 gap-2 rounded-2xl border border-[#e3e8f4] bg-white/70 p-3 sm:grid-cols-[1fr_12rem_auto] dark:border-[#24386f] dark:bg-white/5">
                <label for="att-class" class="sr-only">Class</label>
                <select id="att-class" name="schedule_id" class="{{ $input }}">
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->schedule_id }}" @selected($selected->schedule_id === $schedule->schedule_id)>{{ $classLabel($schedule) }}</option>
                    @endforeach
                </select>
                <label for="att-date" class="sr-only">Date</label>
                <input id="att-date" type="date" name="date" value="{{ $dateValue }}" max="{{ today()->toDateString() }}" class="{{ $input }}">
                <button type="submit" class="btn-navy">Open roster</button>
            </form>
        </div>

        {{-- Summary for the chosen date --}}
        <div class="bento-grid !grid-cols-2 lg:!grid-cols-4">
            @foreach ($summary as $status => $count)
                <article class="bento-card !min-h-0">
                    <div class="flex items-center justify-between">
                        <div class="bento-card__label">{{ $status }}</div>
                        <span class="h-2.5 w-2.5 rounded-full {{ $dot[$status] }}"></span>
                    </div>
                    <p class="bento-card__description mt-1">{{ $records->isEmpty() ? 'Not taken yet' : 'Saved for ' . $date->format('M j') }}</p>
                    <div class="bento-card__value">{{ $records->isEmpty() ? '—' : $count }}</div>
                </article>
            @endforeach
        </div>

        <div class="mx-auto grid w-full max-w-6xl grid-cols-1 items-start gap-3 px-3 pb-3 lg:grid-cols-3">
            {{-- Roster --}}
            <form method="POST" action="{{ route('teacher.attendance.store') }}" class="bento-card !min-h-0 lg:col-span-2 bento-card--static">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $selected->schedule_id }}">
                <input type="hidden" name="attendance_date" value="{{ $dateValue }}">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></span>
                        <div>
                            <div class="bento-card__label">{{ $date->format('l, F j, Y') }}</div>
                            <h2 class="bento-card__title">{{ $selected->subject?->subject_name }} — {{ $selected->section?->section_name }}</h2>
                            <p class="bento-card__description">{{ $students->count() }} {{ Str::plural('student', $students->count()) }} · {{ $records->isEmpty() ? 'not taken yet' : 'saved, you can still update it' }}</p>
                        </div>
                    </div>
                </div>

                <ul class="mt-4 space-y-2">
                    @forelse ($students as $student)
                        @php $current = $records[$student->student_id]->status ?? 'Present'; @endphp
                        <li class="flex flex-col gap-2 rounded-xl border border-[#e3e8f4] bg-white px-3 py-2.5 md:flex-row md:items-center md:justify-between dark:border-slate-700 dark:bg-slate-900/45">
                            <span class="text-sm font-semibold text-ink dark:text-white">{{ $student->user?->last_name }}, {{ $student->user?->first_name }}</span>
                            <div class="flex flex-col gap-1.5 lg:flex-row lg:items-center">
                            <div class="grid grid-cols-4 gap-1.5 md:w-[22rem]" role="radiogroup" aria-label="Status for {{ $student->user?->first_name }}">
                                @foreach (\App\Models\AttendanceRecord::STATUSES as $status)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status[{{ $student->student_id }}]" value="{{ $status }}" class="peer sr-only" @checked($current === $status)>
                                        <span class="flex items-center justify-center rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs font-semibold text-ink/60 transition hover:border-brand/40 peer-focus-visible:ring-3 peer-focus-visible:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 {{ $statusStyle[$status] }}">{{ $status }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <label for="remarks-{{ $student->student_id }}" class="sr-only">Remarks for {{ $student->user?->first_name }}</label>
                            <input type="text" id="remarks-{{ $student->student_id }}" name="remarks[{{ $student->student_id }}]" maxlength="255" placeholder="Remarks (optional)"
                                   value="{{ old('remarks.' . $student->student_id, $records[$student->student_id]->remarks ?? '') }}"
                                   class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-ink placeholder-slate-400 transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 lg:w-44 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500">
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-[#d5dcec] px-4 py-8 text-center text-sm text-ink/55 dark:border-slate-700 dark:text-slate-400">No students are enrolled in this section yet.</li>
                    @endforelse
                </ul>

                @if ($students->isNotEmpty())
                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="btn-navy">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save attendance
                        </button>
                    </div>
                @endif
            </form>

            {{-- Recent sessions --}}
            <aside class="bento-card !min-h-0 bento-card--static">
                <div class="bento-card__label">History</div>
                <h2 class="bento-card__title">Recent sessions</h2>
                <ul class="mt-3 space-y-1.5">
                    @forelse ($recentSessions as $session)
                        @php $sessionDate = \Carbon\Carbon::parse($session->attendance_date); @endphp
                        <li>
                            <a href="{{ route('teacher.attendance.index', ['schedule_id' => $selected->schedule_id, 'date' => $sessionDate->toDateString()]) }}"
                               class="flex items-center justify-between gap-3 rounded-xl px-3 py-2 transition hover:bg-white hover:shadow-sm dark:hover:bg-white/5 {{ $sessionDate->isSameDay($date) ? 'bg-white shadow-sm ring-1 ring-ink/[.06] dark:bg-white/10' : '' }}">
                                <span class="text-sm font-semibold text-ink dark:text-white">{{ $sessionDate->format('D, M j') }}</span>
                                <span class="flex items-center gap-2 text-xs font-semibold">
                                    <span class="text-emerald-600 dark:text-emerald-400" title="Present">{{ (int) $session->present }}</span>
                                    <span class="text-amber-600 dark:text-amber-400" title="Late">{{ (int) $session->late }}</span>
                                    <span class="text-red-600 dark:text-red-400" title="Absent">{{ (int) $session->absent }}</span>
                                    <span class="text-brand dark:text-blue-300" title="Excused">{{ (int) $session->excused }}</span>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="text-sm text-ink/55 dark:text-slate-400">No attendance taken for this class yet.</li>
                    @endforelse
                </ul>
                <p class="mt-3 text-[11px] text-ink/45 dark:text-slate-500">Counts: present · late · absent · excused</p>
            </aside>
        </div>
    @endif
</div>
@endsection
