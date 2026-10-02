{{-- Student: Class Schedule --}}
@extends('layouts.student')
@section('title', 'My Schedule')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $timeRange = fn ($s) => \Carbon\Carbon::parse($s->start_time)->format('h:i A') . ' – ' . \Carbon\Carbon::parse($s->end_time)->format('h:i A');
@endphp

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Schedule</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Your full school-year class timetable.</p>
        </div>
        <div class="bento-grid">
            <article class="bento-card bento-card--full">
                <div class="flex items-start gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                    <div>
                        <div class="bento-card__label">Schedule</div>
                        <h2 class="bento-card__title">Class Timetable</h2>
                        <p class="bento-card__description">Weekly schedule for your enrolled subjects</p>
                    </div>
                </div>

                <div class="st-table-wrap mt-3">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedule as $period)
                                <tr>
                                    <td><span class="inline-flex rounded-full bg-[#eef3fd] px-2.5 py-0.5 text-xs font-semibold text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">{{ $period->day_of_week }}</span></td>
                                    <td class="whitespace-nowrap font-medium text-ink dark:text-white">{{ $timeRange($period) }}</td>
                                    <td class="font-semibold text-ink dark:text-white">{{ $period->subject?->subject_name }}</td>
                                    <td>{{ trim(($period->teacher?->first_name ?? '') . ' ' . ($period->teacher?->last_name ?? '')) ?: '—' }}</td>
                                    <td>{{ $period->room?->room_name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="st-table__empty">
                                        No schedule available yet. Once you're enrolled in a section, your classes will appear here.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </div>
@endsection
