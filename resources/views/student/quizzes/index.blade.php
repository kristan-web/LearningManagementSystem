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
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Quizzes</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Pick a subject to see its available quizzes.</p>
    </div>
    <div class="bento-grid">
        @forelse ($subjects as $entry)
            @php
                $initials = collect(explode(' ', $entry->subject->subject_name))->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->join('');
                $hue = crc32($entry->subject->subject_name) % 360;
            @endphp
            <article class="bento-card bento-card--full" style="--thumb-hue: {{ $hue }}" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-3 text-left">
                    <div class="flex items-center gap-3">
                        <div class="bento-card__thumb !mb-0">{{ Str::upper($initials) }}</div>
                        <div>
                            <div class="bento-card__label">Subject</div>
                            <h2 class="bento-card__title">{{ $entry->subject->subject_name }}</h2>
                            <p class="bento-card__description">{{ $entry->quizzes->count() }} {{ Str::plural('quiz', $entry->quizzes->count()) }} available</p>
                        </div>
                    </div>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-ink/60 ring-1 ring-ink/10 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10">
                        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </button>
                <div x-show="open" x-cloak x-transition class="st-table-wrap mt-4 border-t border-[#eef1f8] pt-2 dark:border-white/10">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Due</th>
                                <th>Attempts</th>
                                <th>Best Score</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry->quizzes as $quiz)
                                @php
                                    $used = $usedAttempts($quiz);
                                    $active = $inProgress($quiz);
                                    $best = $bestAttempt($quiz);
                                    $canAttempt = $active !== null || $used < $quiz->attempts_allowed;
                                @endphp
                                <tr>
                                    <td>
                                        <p class="font-semibold text-ink dark:text-white">{{ $quiz->title }}</p>
                                        <p class="mt-0.5 text-xs text-ink/55 dark:text-slate-400">{{ $quiz->questions_count }} question(s)@if($quiz->time_limit_minutes) &middot; {{ $quiz->time_limit_minutes }} min timer @endif</p>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        {{ $quiz->due_date?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td>{{ $used }} / {{ $quiz->attempts_allowed }}</td>
                                    <td>
                                        @if ($best)
                                            <span class="font-semibold text-ink dark:text-white">{{ $best->score }}%</span>
                                        @else
                                            <span class="text-ink/40 dark:text-slate-500">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($active)
                                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="inline-flex items-center rounded-lg bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-500/20 transition hover:bg-amber-500 hover:text-white dark:text-amber-300">Resume</a>
                                        @elseif ($canAttempt)
                                            <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="btn-navy btn-sm"
                                               data-confirm="{{ $used > 0 ? 'Retake' : 'Start' }} {{ $quiz->title }}?"
                                               data-confirm-text="This uses attempt {{ $used + 1 }} of {{ $quiz->attempts_allowed }}.{{ $quiz->time_limit_minutes ? ' The ' . $quiz->time_limit_minutes . '-minute timer starts right away.' : '' }}"
                                               data-confirm-button="{{ $used > 0 ? 'Retake quiz' : 'Start quiz' }}">{{ $used > 0 ? 'Retake' : 'Start' }}</a>
                                        @elseif ($best)
                                            <a href="{{ route('student.quizzes.results', [$quiz->quiz_id, $best->attempt_id]) }}" class="st-btn-outline btn-sm">Review</a>
                                        @else
                                            <span class="text-xs text-ink/50 dark:text-slate-500">No attempts left</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <article class="bento-card bento-card--full items-center !py-10 text-center">
                <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                <div class="bento-card__label mt-3">Academics</div>
                <h2 class="bento-card__title">No quizzes yet</h2>
                <p class="bento-card__description">Check back when your teachers post new quizzes.</p>
            </article>
        @endforelse
    </div>
</div>
@endsection
