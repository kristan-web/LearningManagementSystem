{{-- Student: Attendance (StudentAttendanceController; records are entered by teachers on their Attendance page) --}}
@extends('layouts.student')
@section('title', 'My Attendance')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    // From StudentAttendanceController:
    //   $records  -> rows with date, subject_name, time_in (class start time), status (Present|Absent|Late|Excused), recorded_by (teacher name), remarks
    //   $summary  -> ['present' => int, 'absent' => int, 'late' => int, 'rate' => int (percent)]
    //   $subjects -> rows with subject_id, subject_name (for the filter)
    $records = $records ?? collect();
    $summary = $summary ?? [];
    $subjects = $subjects ?? collect();

    $statusBadge = [
        'Present' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Absent' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Excused' => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
    ];

    $stats = [
        ['label' => 'Present', 'value' => $summary['present'] ?? '—', 'description' => 'Days marked present', 'icon' => 'M5 13l4 4L19 7'],
        ['label' => 'Absent', 'value' => $summary['absent'] ?? '—', 'description' => 'Days marked absent', 'icon' => 'M6 18 18 6M6 6l12 12'],
        ['label' => 'Late', 'value' => $summary['late'] ?? '—', 'description' => 'Times arrived late', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['label' => 'Attendance Rate', 'value' => isset($summary['rate']) ? $summary['rate'] . '%' : '—', 'description' => 'For the current term', 'icon' => 'M3 3v18h18M7 16l4-4 4 4 5-6'],
    ];

    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:focus:border-blue-400';
@endphp

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Attendance</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Review your attendance history.</p>
        </div>

        {{-- Summary --}}
        <div class="bento-grid !grid-cols-2 lg:!grid-cols-4">
            @foreach ($stats as $stat)
                <article class="bento-card !min-h-0">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="bento-card__label">{{ $stat['label'] }}</div>
                            <p class="bento-card__description mt-1">{{ $stat['description'] }}</p>
                        </div>
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg></span>
                    </div>
                    <div class="bento-card__value">{{ $stat['value'] }}</div>
                </article>
            @endforeach
        </div>

        <div class="bento-grid">
            <article class="bento-card bento-card--full">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                        <div>
                            <div class="bento-card__label">Academics</div>
                            <h2 class="bento-card__title">Attendance Records</h2>
                            <p class="bento-card__description">Your daily attendance across subjects</p>
                        </div>
                    </div>

                    {{-- Filters (plain GET form: read request('subject') / request('month') in the controller) --}}
                    <form method="GET" action="{{ route('student.attendance.index') }}" class="grid grid-cols-1 gap-2 sm:grid-cols-[12rem_10rem_auto]">
                        <label class="sr-only" for="att-subject">Subject</label>
                        <select id="att-subject" name="subject" class="{{ $input }}">
                            <option value="">All subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->subject_id }}" @selected(request('subject') == $subject->subject_id)>{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="att-month">Month</label>
                        <input id="att-month" type="month" name="month" value="{{ request('month') }}" class="{{ $input }}">
                        <button type="submit" class="btn-navy">Filter</button>
                    </form>
                </div>

                <div class="st-table-wrap mt-3">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Class Time</th>
                                <th>Status</th>
                                <th>Recorded By</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    <td class="whitespace-nowrap font-semibold text-ink dark:text-white">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                    <td>{{ $record->subject_name }}</td>
                                    <td class="whitespace-nowrap">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '—' }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$record->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $record->status }}</span>
                                    </td>
                                    <td>{{ $record->recorded_by ?: '—' }}</td>
                                    <td class="text-ink/70 dark:text-slate-300">{{ $record->remarks ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="st-table__empty">
                                        No attendance records yet. Your attendance will appear here once your teachers start checking it.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="mt-3 flex flex-wrap gap-4 text-xs font-medium text-ink/60 dark:text-slate-400">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Present</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> Absent</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Late</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-brand"></span> Excused</span>
                </div>
            </article>
        </div>
    </div>
@endsection
