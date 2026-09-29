{{-- Student: Quizzes --}}
@extends('layouts.student')
@section('title', 'My Quizzes')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $firstAttempt = fn ($quiz) => $quiz->attempts->first();
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Quizzes</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Attempt and review your quizzes.</p>
    </div>
    <div class="bento-grid">
        <article class="bento-card bento-card--span-2">
            <div class="bento-card__label">Academics</div>
            <h2 class="bento-card__title">Quiz List</h2>
            <p class="bento-card__description">Quizzes across your enrolled subjects</p>

            <table class="mt-4 w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-xs uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-2">Quiz</th>
                        <th class="px-3 py-2">Subject</th>
                        <th class="px-3 py-2">Due</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Score</th>
                        <th class="px-3 py-2 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($quizzes as $quiz)
                        @php $attempt = $firstAttempt($quiz); @endphp
                        <tr class="align-top">
                            <td class="px-3 py-2.5">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $quiz->title }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $quiz->questions_count }} question{{ $quiz->questions_count === 1 ? '' : 's' }}</p>
                            </td>
                            <td class="px-3 py-2.5 text-sm text-gray-600 dark:text-gray-300">{{ $quiz->schedule?->subject?->subject_name }}</td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                @if ($quiz->due_date)
                                    {{ $quiz->due_date->format('M d, Y') }}<br>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $quiz->due_date->format('h:i A') }}</span>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="px-3 py-2.5">
                                @if ($attempt?->submitted_at)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Submitted</span>
                                @elseif ($attempt)
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">In progress</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">Not started</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-sm">
                                @if ($attempt?->score !== null)
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $attempt->score }}</span>
                                    <span class="text-gray-400 dark:text-gray-500">/ {{ $quiz->questions_count }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right">
                                @if ($attempt?->submitted_at)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Submitted {{ $attempt->submitted_at->format('M d, Y') }}</span>
                                @else
                                    <a href="{{ route('student.quizzes.show', $quiz->quiz_id) }}"
                                       class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white dark:bg-emerald-500/10 dark:text-emerald-300">
                                        {{ $attempt ? 'Resume' : 'Start' }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No quizzes yet. Check back when your teachers post new work.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </article>
    </div>
</div>
@endsection

