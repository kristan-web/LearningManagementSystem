{{-- Teacher: Quiz results (TeacherQuizReviewController@results) — read-only --}}
@extends('layouts.teacher')
@section('title', 'Quiz Results')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $submittedStudents = $rows->filter(fn ($r) => $r->attempts->isNotEmpty())->count();
    $bestScores = $rows->pluck('best')->filter()->pluck('score');
    $average = $bestScores->isNotEmpty() ? round($bestScores->avg(), 1) : null;
    $rateTone = fn ($rate) => $rate === null ? 'bg-slate-300 dark:bg-slate-600' : ($rate >= 75 ? 'bg-emerald-500' : ($rate >= 50 ? 'bg-amber-500' : 'bg-red-500'));
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('teacher.quizzes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to quizzes
            </a>
            <h1 class="mt-2 font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">{{ $quiz->title }}</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">{{ $quiz->schedule?->subject?->subject_name }} — {{ $quiz->schedule?->section?->section_name }} · {{ $questions->count() }} {{ Str::plural('question', $questions->count()) }}</p>
        </div>
        <a href="{{ route('teacher.quizzes.edit', $quiz->quiz_id) }}" class="st-btn-outline">Edit details</a>
    </div>

    <div class="bento-grid !grid-cols-2 lg:!grid-cols-4">
        @foreach ([
            ['Students', $rows->count(), 'Enrolled in this section'],
            ['Took the quiz', $submittedStudents . ' / ' . $rows->count(), 'At least one submitted attempt'],
            ['Attempts', $attempts->count(), 'Submitted attempts in total'],
            ['Average', $average !== null ? $average . '%' : '—', 'Of each student\'s best score'],
        ] as [$label, $value, $hint])
            <article class="bento-card !min-h-0">
                <div class="bento-card__label">{{ $label }}</div>
                <p class="bento-card__description mt-1">{{ $hint }}</p>
                <div class="bento-card__value">{{ $value }}</div>
            </article>
        @endforeach
    </div>

    <div class="mx-auto grid w-full max-w-6xl grid-cols-1 items-start gap-3 px-3 pb-3 lg:grid-cols-3">
        {{-- Students --}}
        <article class="bento-card bento-card--static !min-h-0 lg:col-span-2">
            <div class="bento-card__label">Students</div>
            <h2 class="bento-card__title">Scores and answers</h2>
            <div class="st-table-wrap mt-3">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th class="text-center">Attempts</th>
                            <th class="text-center">Best</th>
                            <th class="text-right">Answers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="font-semibold text-ink dark:text-white">{{ $row->student->user?->last_name }}, {{ $row->student->user?->first_name }}</td>
                                <td class="text-center">{{ $row->attempts->count() }} / {{ $quiz->attempts_allowed }}</td>
                                <td class="text-center">
                                    @if ($row->best)
                                        <span class="font-display text-base font-bold {{ $row->best->score >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ rtrim(rtrim(number_format($row->best->score, 2), '0'), '.') }}%</span>
                                    @else
                                        <span class="text-ink/35 dark:text-slate-600">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-1.5">
                                        @forelse ($row->attempts as $i => $attempt)
                                            <a href="{{ route('teacher.quizzes.attempt', [$quiz->quiz_id, $attempt->attempt_id]) }}" class="st-btn-outline btn-sm">Attempt {{ $i + 1 }}</a>
                                        @empty
                                            <span class="text-xs text-ink/45 dark:text-slate-500">Not taken</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="st-table__empty">No students are enrolled in this section yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        {{-- Per-question correct rate --}}
        <aside class="bento-card bento-card--static !min-h-0">
            <div class="bento-card__label">Questions</div>
            <h2 class="bento-card__title">How the class did</h2>
            <ol class="mt-3 space-y-3">
                @forelse ($questionStats as $i => $stat)
                    <li>
                        <div class="flex items-start justify-between gap-3 text-sm">
                            <p class="text-ink/80 dark:text-slate-300"><span class="font-semibold text-ink dark:text-white">{{ $i + 1 }}.</span> {{ Str::limit($stat->question->question_text, 80) }}</p>
                            <span class="shrink-0 text-xs font-bold text-ink dark:text-white">{{ $stat->rate !== null ? $stat->rate . '%' : '—' }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-[#e8ecf5] dark:bg-white/10">
                            <div class="h-full rounded-full {{ $rateTone($stat->rate) }}" style="width: {{ $stat->rate ?? 0 }}%"></div>
                        </div>
                        <p class="mt-1 text-[11px] text-ink/45 dark:text-slate-500">{{ $stat->correct }} of {{ $stat->answered }} correct</p>
                    </li>
                @empty
                    <li class="text-sm text-ink/55 dark:text-slate-400">This quiz has no questions.</li>
                @endforelse
            </ol>
        </aside>
    </div>
</div>
@endsection
