{{-- Student: Assignment post --}}
@extends('layouts.student')
@section('title', $assignment->title)

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $statusBadge = [
        'Submitted' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Graded' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <a href="{{ route('student.assignments.index') }}" class="text-sm text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&larr; Back to assignments</a>

    {{-- Post card --}}
    <article class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ $assignment->schedule?->subject?->subject_name }}
        </p>
        <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $assignment->title }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Due {{ $assignment->due_date?->format('M d, Y') }} at {{ $assignment->due_date?->format('h:i A') }}
            &middot; Max {{ $assignment->max_score }} points
        </p>

        @if ($assignment->instructions)
            <p class="mt-4 whitespace-pre-line text-sm text-gray-700 dark:text-gray-300">{{ $assignment->instructions }}</p>
        @endif

        <div class="mt-5 border-t border-gray-100 pt-4 dark:border-white/10">
            @if ($submission)
                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge[$submission->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                    {{ $submission->status }}
                </span>
                <span class="ml-2 text-xs text-gray-400 dark:text-gray-500">Submitted {{ $submission->submitted_at?->format('M d, Y') }}</span>
                @if ($submission->score !== null)
                    <span class="ml-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $submission->score }} / {{ $assignment->max_score }}</span>
                @endif
            @else
                <form method="POST" action="{{ route('student.assignments.submit', $assignment->assignment_id) }}"
                      enctype="multipart/form-data" class="inline-flex items-center gap-2">
                    @csrf
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip" class="max-w-48 text-xs">
                    <button type="submit" class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-500 hover:text-white dark:bg-emerald-500/10 dark:text-emerald-300">Submit</button>
                </form>
            @endif
        </div>
    </article>

    @include('partials.assignment-discussion', ['assignment' => $assignment])
</div>
@endsection
