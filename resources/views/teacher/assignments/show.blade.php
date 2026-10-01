{{-- Teacher: Assignment post --}}
@extends('layouts.teacher')
@section('title', $assignment->title)

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">&larr; Back to assignments</a>

    {{-- Post card --}}
    <article class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
            {{ $assignment->schedule?->subject?->subject_name }} &middot; {{ $assignment->schedule?->section?->section_name }}
        </p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $assignment->title }}</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            Due {{ $assignment->due_date?->format('M d, Y') }} at {{ $assignment->due_date?->format('h:i A') }}
            &middot; Max {{ $assignment->max_score }} points
            @if ($assignment->isPastDue())
                <span class="ml-1 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-500/15 dark:text-red-300">Overdue</span>
            @endif
        </p>

        @if ($assignment->instructions)
            <p class="mt-4 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $assignment->instructions }}</p>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-700" x-data="{ extending: false }">
            <span class="text-sm text-slate-600 dark:text-slate-300">{{ $assignment->submissions_count }} submitted</span>
            @if ($assignment->awaiting_grading_count > 0)
                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                    {{ $assignment->awaiting_grading_count }} to grade
                </span>
            @endif
            <a href="{{ route('teacher.assignments.submissions', $assignment->assignment_id) }}"
               class="ml-auto rounded-lg border border-blue-100 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-500 hover:text-white dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
                View submissions
            </a>
            <button type="button" @click="extending = !extending"
                    class="rounded-lg border border-amber-200 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-500 hover:text-white dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                Extend deadline
            </button>
            <a href="{{ route('teacher.assignments.edit', $assignment->assignment_id) }}"
               class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                Edit
            </a>
            <form x-show="extending" x-cloak x-transition
                  method="POST" action="{{ route('teacher.assignments.extend', $assignment->assignment_id) }}"
                  class="mt-3 flex w-full items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-700">
                @csrf
                @method('PUT')
                <label for="extend_due_date" class="text-xs font-medium text-slate-600 dark:text-slate-300">New due date &amp; time</label>
                <input type="datetime-local" name="due_date" id="extend_due_date" required
                       class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:shadow-md">Save</button>
            </form>
        </div>
    </article>

    @include('partials.assignment-discussion', ['assignment' => $assignment])
</div>
@endsection
