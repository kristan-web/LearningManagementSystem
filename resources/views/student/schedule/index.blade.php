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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Schedule</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your full school-year class timetable.</p>
        </div>
        <div class="bento-grid">
            <article class="bento-card bento-card--span-2">
                <div class="bento-card__label">Schedule</div>
                <h2 class="bento-card__title">Class Timetable</h2>
                <p class="bento-card__description">Weekly schedule for your enrolled subjects</p>

                <table class="mt-4 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            <th class="px-3 py-2">Day</th>
                            <th class="px-3 py-2">Time</th>
                            <th class="px-3 py-2">Subject</th>
                            <th class="px-3 py-2">Teacher</th>
                            <th class="px-3 py-2">Room</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse ($schedule as $period)
                            <tr class="align-top">
                                <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">{{ $period->day_of_week }}</td>
                                <td class="px-3 py-2.5 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $timeRange($period) }}</td>
                                <td class="px-3 py-2.5 text-gray-600 dark:text-gray-300">{{ $period->subject?->subject_name }}</td>
                                <td class="px-3 py-2.5 text-gray-600 dark:text-gray-300">{{ trim(($period->teacher?->first_name ?? '') . ' ' . ($period->teacher?->last_name ?? '')) ?: '—' }}</td>
                                <td class="px-3 py-2.5 text-gray-600 dark:text-gray-300">{{ $period->room?->room_name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No schedule available yet. Once you're enrolled in a section, your classes will appear here.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </article>
        </div>
    </div>
@endsection

