{{-- Teacher: New Quiz --}}
@extends('layouts.teacher')
@section('title', 'New Quiz')

@php
    $card = 'rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800';
    $label = 'mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );
@endphp

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">New Quiz</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create a quiz for one of your classes.</p>
    </div>

    @if ($schedules->count() === 0)
        <div class="{{ $card }} p-6">
            <p class="text-sm text-slate-600 dark:text-slate-300">You don't have any classes yet. Quizzes are posted per class, so a schedule is required.</p>
        </div>
    @else
        @include('teacher.quizzes._form', [
            'action' => route('teacher.quizzes.store'),
            'method' => 'POST',
            'quiz' => null,
            'submitLabel' => 'Create Quiz',
        ])
    @endif
</div>
@endsection
