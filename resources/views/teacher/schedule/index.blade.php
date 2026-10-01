{{-- Teacher: Class Schedule --}}
@extends('layouts.teacher')
@section('title', 'Teaching Schedule')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $timeRange = fn ($s) => \Carbon\Carbon::parse($s->start_time)->format('h:i A') . ' – ' . \Carbon\Carbon::parse($s->end_time)->format('h:i A');
    $sectionLabel = fn ($section) => trim('Grade ' . ($section?->grade_level ?? '') . ' - ' . ($section?->section_name ?? ''));
    $today = now()->format('l');
@endphp

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Teaching Schedule</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Your full school-year teaching timetable. Today's classes are highlighted.</p>
        </div>

        <div class="bento-grid">
            <article class="bento-card bento-card--full">
                <div class="flex items-start gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                    <div>
                        <div class="bento-card__label">Schedule</div>
                        <h2 class="bento-card__title">Weekly Timetable</h2>
                        <p class="bento-card__description">{{ $schedule->count() }} {{ Str::plural('period', $schedule->count()) }} a week</p>
                    </div>
                </div>

                <div class="st-table-wrap mt-3">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Section</th>
                                <th>Room</th>
                                <th class="text-right">Class</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedule as $period)
                                @php $isToday = $period->day_of_week === $today; @endphp
                                <tr>
                                    <td>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isToday ? 'bg-gold/20 text-gold-deep dark:bg-gold/15 dark:text-gold' : 'bg-[#eef3fd] text-brand-deep dark:bg-blue-500/15 dark:text-blue-300' }}">{{ $period->day_of_week }}{{ $isToday ? ' · Today' : '' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap font-medium text-ink dark:text-white">{{ $timeRange($period) }}</td>
                                    <td class="font-semibold text-ink dark:text-white">{{ $period->subject?->subject_name }}</td>
                                    <td>{{ $sectionLabel($period->section) }}</td>
                                    <td>{{ $period->room?->room_name ?? '—' }}</td>
                                    <td class="whitespace-nowrap text-right">
                                        <a href="{{ route('teacher.attendance.index', ['schedule_id' => $period->schedule_id]) }}" class="st-btn-outline btn-sm">Attendance</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="st-table__empty">No schedule assigned yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </div>
@endsection
