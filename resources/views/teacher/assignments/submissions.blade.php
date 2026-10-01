{{-- Teacher: Assignment submissions --}}
@extends('layouts.teacher')
@section('title', 'Submissions')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $statusBadge = [
        'Submitted' => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Graded' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];
    $submittedCount = $submissions->count();
    $gradedCount = $submissions->where('status', 'Graded')->count();
@endphp

@section('content')
<div class="bento-content">
    {{-- Page header --}}
    <div class="bento-header">
        <a href="{{ route('teacher.assignments.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to assignments
        </a>
        <h1 class="mt-2 font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">{{ $assignment->title }}</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">
            {{ $assignment->schedule?->subject?->subject_name }} · {{ $assignment->schedule?->section?->section_name }}
            &middot; Due {{ $assignment->due_date?->format('M d, Y') }} at {{ $assignment->due_date?->format('h:i A') }}
            &middot; Max {{ $assignment->max_score }} points
        </p>
    </div>

    <div class="bento-grid">
        @foreach ([
            ['Students', $students->count(), 'Enrolled in this section'],
            ['Submitted', $submittedCount, 'Turned in a file'],
            ['Graded', $gradedCount . ' / ' . $submittedCount, 'Submissions with a score'],
        ] as [$label, $value, $hint])
            <article class="bento-card !min-h-0">
                <div class="bento-card__label">{{ $label }}</div>
                <p class="bento-card__description mt-1">{{ $hint }}</p>
                <div class="bento-card__value">{{ $value }}</div>
            </article>
        @endforeach
    </div>

    <div class="bento-grid">
        <article class="bento-card bento-card--full">
            <div class="st-table-wrap">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>File</th>
                            <th class="text-right">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            @php $submission = $submissions->firstWhere('student_id', $student->student_id); @endphp
                            <tr>
                                <td class="font-semibold text-ink dark:text-white">
                                    {{ $student->user?->last_name }}, {{ $student->user?->first_name }}
                                </td>
                                @if ($submission)
                                    <td class="whitespace-nowrap">
                                        {{ $submission->submitted_at?->format('M d, Y') }}<br>
                                        <span class="text-xs text-ink/50 dark:text-slate-500">{{ $submission->submitted_at?->format('h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$submission->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                            {{ $submission->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($submission->file_url)
                                            <a href="{{ route('teacher.submissions.download', $submission->submission_id) }}" class="st-btn-outline btn-sm">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                                Download
                                            </a>
                                        @else
                                            <span class="text-xs text-ink/45 dark:text-slate-500">No file</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($submission->status === 'Graded')
                                            <span class="font-display text-base font-bold text-emerald-600 dark:text-emerald-400">{{ $submission->score }}</span>
                                            <span class="text-xs text-ink/50 dark:text-slate-500">/ {{ $assignment->max_score }}</span>
                                        @else
                                            <form method="POST" action="{{ route('teacher.submissions.grade', $submission->submission_id) }}"
                                                  class="inline-flex items-center gap-2"
                                                  data-confirm="Save this grade?" data-confirm-text="The score is final once saved and the student will see it." data-confirm-button="Save grade">
                                                @csrf
                                                @method('PUT')
                                                <label class="sr-only" for="score-{{ $submission->submission_id }}">Score</label>
                                                <input id="score-{{ $submission->submission_id }}" type="number" name="score" min="0" max="{{ $assignment->max_score }}" step="0.01" required
                                                       value="{{ $submission->score ?? '' }}" placeholder="0–{{ $assignment->max_score }}"
                                                       class="w-24 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-ink focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                                <button type="submit" class="btn-navy btn-sm">Grade</button>
                                            </form>
                                        @endif
                                    </td>
                                @else
                                    <td colspan="4" class="text-ink/45 dark:text-slate-500">No submission yet</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="st-table__empty">No students enrolled in this section.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</div>
@endsection
