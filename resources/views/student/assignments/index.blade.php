{{-- Student: Assignments --}}
@extends('layouts.student')
@section('title', 'My Assignments')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $statusBadge = [
        'Submitted' => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Graded' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];

    $firstSubmission = fn ($assignment) => $assignment->submissions->first();
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header">
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Assignments</h1>
        <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Track due dates, submit your work, and see your scores.</p>
    </div>
    <div class="bento-grid">
        <article class="bento-card bento-card--full">
            <div class="flex items-start gap-3">
                <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/></svg></span>
                <div>
                    <div class="bento-card__label">Academics</div>
                    <h2 class="bento-card__title">Assignment List</h2>
                    <p class="bento-card__description">Assignments across your enrolled subjects</p>
                </div>
            </div>

            <div class="st-table-wrap mt-3">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Subject</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th class="text-right">Submit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignments as $assignment)
                            @php $submission = $firstSubmission($assignment); @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('student.assignments.show', $assignment->assignment_id) }}" class="font-semibold text-ink hover:text-brand hover:underline dark:text-white">{{ $assignment->title }}</a>
                                    @if ($assignment->instructions)
                                        <p class="mt-0.5 line-clamp-2 text-xs text-ink/55 dark:text-slate-400">{{ $assignment->instructions }}</p>
                                    @endif
                                </td>
                                <td>{{ $assignment->schedule?->subject?->subject_name }}</td>
                                <td class="whitespace-nowrap">
                                    {{ $assignment->due_date?->format('M d, Y') }}<br>
                                    <span class="text-xs text-ink/50 dark:text-slate-500">{{ $assignment->due_date?->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @if ($submission)
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$submission->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                            {{ $submission->status }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 dark:bg-slate-700 dark:text-slate-300">Not submitted</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($submission?->score !== null)
                                        <span class="font-semibold text-ink dark:text-white">{{ $submission->score }}</span>
                                        <span class="text-ink/50 dark:text-slate-500">/ {{ $assignment->max_score }}</span>
                                    @else
                                        <span class="text-ink/40 dark:text-slate-500">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($submission)
                                        <span class="text-xs text-ink/50 dark:text-slate-500">Submitted {{ $submission->submitted_at?->format('M d, Y') }}</span>
                                    @else
                                        <form method="POST" action="{{ route('student.assignments.submit', $assignment->assignment_id) }}"
                                              enctype="multipart/form-data" class="inline-flex items-center gap-2"
                                              data-confirm="Submit this assignment?" data-confirm-text="You can only submit once, so make sure you picked the right file." data-confirm-button="Yes, submit">
                                            @csrf
                                            <input type="file" name="file" required accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip" class="st-file max-w-48">
                                            <button type="submit" class="btn-navy btn-sm">Submit</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="st-table__empty">
                                    No assignments yet. Check back when your teachers post new work.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</div>
@endsection
