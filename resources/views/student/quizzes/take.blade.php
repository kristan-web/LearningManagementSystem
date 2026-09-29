{{-- Student: Take Quiz --}}
@extends('layouts.student')
@section('title', $quiz->title)

@php
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
@endphp

@section('content')
<div class="mx-auto w-full max-w-2xl space-y-6 pt-8">
    <div>
        <a href="{{ route('student.quizzes.index') }}" class="text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&larr; Back to quizzes</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $quiz->title }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $quiz->schedule?->subject?->subject_name }}
            @if ($quiz->time_limit_minutes)
                &middot; Time limit: {{ $quiz->time_limit_minutes }} minutes
            @endif
        </p>
    </div>

    <form method="POST" action="{{ route('student.quizzes.submit', $quiz->quiz_id) }}"
          class="space-y-4 rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        @csrf

        @foreach ($quiz->questions as $index => $question)
            <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-600">
                <p class="font-medium text-slate-800 dark:text-slate-100">{{ $index + 1 }}. {{ $question->question_text }}</p>

                @if ($question->question_type === 'multiple_choice')
                    <div class="mt-3 space-y-2">
                        @foreach ($question->options ?? [] as $option)
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <input type="radio" name="answers[{{ $question->question_id }}]" value="{{ $option }}" required>
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>
                @elseif ($question->question_type === 'true_false')
                    <div class="mt-3 space-y-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="radio" name="answers[{{ $question->question_id }}]" value="True" required>
                            True
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="radio" name="answers[{{ $question->question_id }}]" value="False" required>
                            False
                        </label>
                    </div>
                @else
                    <input type="text" name="answers[{{ $question->question_id }}]" required class="{{ $input }} mt-3" placeholder="Your answer">
                @endif
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" onclick="return confirm('Submit your answers? You cannot change them after submitting.')"
                    class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">
                Submit Quiz
            </button>
        </div>
    </form>
</div>
@endsection
