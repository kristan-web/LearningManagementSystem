{{-- Teacher: Attendance --}}
@extends('layouts.teacher')
@section('title', 'Attendance')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';

    $studentName = fn ($student) => $student?->user
        ? trim($student->user->last_name . ', ' . $student->user->first_name)
        : ($student?->student_number ?? 'Unknown student');

    $scheduleLabel = fn ($s) => trim(
        ($s->subject?->subject_name ?? 'Subject') . ' — Grade ' . $s->section?->grade_level . ' ' . $s->section?->section_name
        . ' (' . $s->day_of_week . ' ' . \Illuminate\Support\Carbon::parse($s->start_time)->format('g:ia') . ')'
    );

    $statuses = ['Present', 'Late', 'Absent'];
    $statusPill = [
        'Present' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-500/15 dark:peer-checked:text-emerald-300',
        'Late' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:border-amber-400 dark:peer-checked:bg-amber-500/15 dark:peer-checked:text-amber-300',
        'Absent' => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:border-red-400 dark:peer-checked:bg-red-500/15 dark:peer-checked:text-red-300',
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-5xl space-y-6 pt-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Attendance</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mark attendance for a class period and date.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <section class="{{ $card }}">
        {{-- Period & date filters --}}
        <form method="GET" action="{{ route('teacher.attendance.index') }}" class="grid grid-cols-1 gap-3 border-b border-blue-100 p-4 sm:grid-cols-3 dark:border-slate-700">
            <div class="sm:col-span-2">
                <select name="schedule_id" class="{{ $input }}" onchange="this.form.submit()">
                    @forelse ($schedules as $s)
                        <option value="{{ $s->schedule_id }}" @selected((string) $selectedScheduleId === (string) $s->schedule_id)>
                            {{ $scheduleLabel($s) }}
                        </option>
                    @empty
                        <option value="">No classes scheduled</option>
                    @endforelse
                </select>
            </div>
            <div>
                <input type="date" name="date" value="{{ $date }}" class="{{ $input }}" onchange="this.form.submit()">
            </div>
        </form>

        {{-- Roster --}}
        @if ($schedule === null)
            <div class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                You have no scheduled classes yet.
            </div>
        @else
            <form method="POST" action="{{ route('teacher.attendance.store') }}">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $schedule->schedule_id }}">
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                                <th class="px-5 py-3">Student</th>
                                <th class="px-5 py-3">LRN</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($students as $i => $row)
                                @php $student = $row['student']; @endphp
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
                                        <input type="hidden" name="records[{{ $i }}][student_id]" value="{{ $student?->student_id }}">
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-xs text-slate-600 dark:text-slate-300">{{ $student?->lrn }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="inline-flex gap-1.5">
                                            @foreach ($statuses as $status)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="records[{{ $i }}][status]" value="{{ $status }}" class="peer sr-only" required
                                                        @checked(old("records.$i.status", $row['status']) === $status)>
                                                    <span class="flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-slate-400 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 {{ $statusPill[$status] }}">
                                                        {{ $status }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                        No students enrolled in this section.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($students->isNotEmpty())
                    <div class="flex justify-end border-t border-blue-100 px-5 py-4 dark:border-slate-700">
                        <button type="submit" class="btn-navy">Save Attendance</button>
                    </div>
                @endif
            </form>
        @endif
    </section>
</div>
@endsection
