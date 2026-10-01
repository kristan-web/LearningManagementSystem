{{-- Teacher: Assignment submissions --}}
@extends('layouts.teacher')
@section('title', 'Submissions')

@php
    $statusBadge = [
        'Submitted' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        'Late' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Graded' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    {{-- Page header --}}
    <div class="flex flex-col gap-2">
        <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">&larr; Back to assignments</a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $assignment->title }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            {{ $assignment->schedule?->subject?->subject_name }} · {{ $assignment->schedule?->section?->section_name }}
            &middot; Due {{ $assignment->due_date?->format('M d, Y') }} at {{ $assignment->due_date?->format('h:i A') }}
            &middot; Max {{ $assignment->max_score }} points
        </p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Submitted</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">File</th>
                    <th class="px-4 py-3 text-right">Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($students as $student)
                    @php $submission = $submissions->firstWhere('student_id', $student->student_id); @endphp
                    <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 dark:text-slate-100">
                                {{ $student->user?->last_name }}, {{ $student->user?->first_name }}
                            </p>
                        </td>
                        @if ($submission)
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                {{ $submission->submitted_at?->format('M d, Y') }} {{ $submission->submitted_at?->format('h:i A') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge[$submission->status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $submission->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($submission->file_url)
                                    <a href="{{ route('teacher.submissions.download', $submission->submission_id) }}"
                                       class="text-xs font-semibold text-blue-600 transition hover:underline dark:text-blue-400">Download</a>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500">No file</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($submission->status === 'Graded')
                                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $submission->score }} / {{ $assignment->max_score }}</span>
                                @else
                                    <form method="POST" action="{{ route('teacher.submissions.grade', $submission->submission_id) }}"
                                          class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="score" min="0" max="{{ $assignment->max_score }}" step="0.01" required
                                               value="{{ $submission->score ?? '' }}"
                                               class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                        <select name="status" title="Status"
                                                class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                            <option value="Graded" {{ $submission->status === 'Graded' ? 'selected' : '' }}>Graded</option>
                                            <option value="Submitted" {{ $submission->status === 'Submitted' ? 'selected' : '' }}>Submitted</option>
                                            <option value="Late" {{ $submission->status === 'Late' ? 'selected' : '' }}>Late</option>
                                        </select>
                                        <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:shadow-md">Grade</button>
                                    </form>
                                @endif
                            </td>
                        @else
                            <td colspan="4" class="px-4 py-3 text-sm text-slate-400 dark:text-slate-500">No submission yet</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No students enrolled in this section.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection