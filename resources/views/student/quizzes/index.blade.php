{{-- Student: Quizzes --}}
@extends('layouts.student')
@section('title', 'My Quizzes')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $usedAttempts = fn ($quiz) => $quiz->attempts->filter(fn ($a) => $a->submitted_at !== null)->count();
    $inProgress = fn ($quiz) => $quiz->attempts->first(fn ($a) => $a->submitted_at === null);
    $bestAttempt = fn ($quiz) => $quiz->attempts->filter(fn ($a) => $a->submitted_at !== null)->sortByDesc('score')->first();
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Quizzes</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pick a subject to see its available quizzes.</p>
    </div>
    <div class="bento-grid">
        @forelse ($subjects as $entry)
            @php
                $initials = collect(explode(' ', $entry->subject->subject_name))->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->join('');
                $hue = crc32($entry->subject->subject_name) % 360;
            @endphp
            <article class="bento-card bento-card--span-2" style="--thumb-hue: {{ $hue }}" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex w-full items-start justify-between gap-3 text-left">
                    <div>
                        <div class="bento-card__thumb">{{ Str::upper($initials) }}</div>
                        <div class="bento-card__label">Subject</div>
                        <h2 class="bento-card__title">{{ $entry->subject->subject_name }}</h2>
                        <p class="bento-card__description">{{ $entry->quizzes->count() }} {{ Str::plural('quiz', $entry->quizzes->count()) }} available</p>
                    </div>
                    <svg class="mt-1 h-5 w-5 shrink-0 text-gray-400 transition-transform dark:text-gray-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak x-transition class="mt-4 border-t border-gray-100 pt-3 dark:border-white/10">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                <th class="px-3 py-2">Quiz</th>
                                <th class="px-3 py-2">Due</th>
                                <th class="px-3 py-2">Attempts</th>
                                <th class="px-3 py-2">Best Score</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($entry->quizzes as $quiz)
                                @php
                                    $used = $usedAttempts($quiz);
                                    $active = $inProgress($quiz);
                                    $best = $bestAttempt($quiz);
                                    $canAttempt = $active !== null || $used < $quiz->attempts_allowed;
                                @endphp
                                <tr class="align-top">
                                    <td class="px-3 py-2.5">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $quiz->title }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $quiz->questions_count }} question(s)@if($quiz->time_limit_minutes) &middot; {{ $quiz->time_limit_minutes }} min timer @endif</p>
                                    </td>
                                    <td class="px-3 py-2.5 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                        {{ $quiz->due_date?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2.5 text-sm text-gray-600 dark:text-gray-300">{{ $used }} / {{ $quiz->attempts_allowed }}</td>
                                    <td class="px-3 py-2.5 text-sm">
                                        @if ($best)
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $best->score }}%</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        @if ($active)
                                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="rounded-lg bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-600 transition hover:bg-amber-500 hover:text-white dark:bg-amber-500/10 dark:text-amber-300">Resume</a>
                                        @elseif ($canAttempt)
                                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white dark:bg-emerald-500/10 dark:text-emerald-300">{{ $used > 0 ? 'Retake' : 'Start' }}</a>
                                        @elseif ($best)
                                            <a href="{{ route('student.quizzes.results', [$quiz->quiz_id, $best->attempt_id]) }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">Review</a>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">No attempts left</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <article class="bento-card bento-card--span-2">
                <div class="bento-card__label">Academics</div>
                <h2 class="bento-card__title">No quizzes yet</h2>
                <p class="bento-card__description">Check back when your teachers post new quizzes.</p>
            </article>
        @endforelse
    </div>
</div>
@endsection


