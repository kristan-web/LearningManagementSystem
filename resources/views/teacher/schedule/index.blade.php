{{-- Teacher: Class Schedule --}}
@extends('layouts.teacher')
@section('title', 'Class Schedule')

@php
    $timeRange = fn ($s) => \Carbon\Carbon::parse($s->start_time)->format('h:i A') . ' – ' . \Carbon\Carbon::parse($s->end_time)->format('h:i A');
    $sectionLabel = fn ($section) => trim(($section?->grade_level ?? '') . ' - ' . ($section?->section_name ?? ''));
@endphp

@section('content')
    <div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">My Schedule</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your full school-year teaching timetable.</p>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                        <th class="px-4 py-3">Day</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Section</th>
                        <th class="px-4 py-3">Room</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($schedule as $period)
                        <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ $period->day_of_week }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">{{ $timeRange($period) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $period->subject?->subject_name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $sectionLabel($period->section) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $period->room?->room_name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No schedule assigned yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
