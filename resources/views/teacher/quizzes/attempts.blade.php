{{-- Teacher: Quiz attempts --}}
@extends('layouts.teacher')
@section('title', 'Quiz Attempts')

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    <div class="flex flex-col gap-2">
        <a href="{{ route('teacher.quizzes.index') }}" class="text-sm text-slate-500 transition hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">&larr; Back to quizzes</a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $quiz->title }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            {{ $quiz->schedule?->subject?->subject_name }} · {{ $quiz->schedule?->section?->section_name }}
            &middot; {{ $totalQuestions }} question{{ $totalQuestions === 1 ? '' : 's' }}
            @if ($quiz->due_date)
                &middot; Due {{ $quiz->due_date->format('M d, Y') }} at {{ $quiz->due_date->format('h:i A') }}
            @endif
        </p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-blue-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-600 dark:text-slate-400">
                    <th class="px-4 py-3">Student</th>
                    <th class="px-4 py-3">Submitted</th>
                    <th class="px-4 py-3 text-right">Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($students as $student)
                    @php $attempt = $attempts->firstWhere('student_id', $student->student_id); @endphp
                    <tr class="align-top hover:bg-slate-50 dark:hover:bg-slate-700/40">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 dark:text-slate-100">
                                {{ $student->user?->last_name }}, {{ $student->user?->first_name }}
                            </p>
                        </td>
                        @if ($attempt?->submitted_at)
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                {{ $attempt->submitted_at->format('M d, Y') }} {{ $attempt->submitted_at->format('h:i A') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($attempt->score !== null)
                                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">{{ $attempt->score }} / {{ $totalQuestions }}</span>
                                @else
                                    <form method="POST" action="{{ route('teacher.quiz-attempts.grade', $attempt->attempt_id) }}"
                                          class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="score" min="0" max="{{ $totalQuestions }}" step="0.01" required
                                               value="{{ $attempt->score ?? '' }}"
                                               class="w-20 rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                        <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:shadow-md">Grade</button>
                                    </form>
                                @endif
                            </td>
                        @else
                            <td colspan="2" class="px-4 py-3 text-sm text-slate-400 dark:text-slate-500">Not attempted yet</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                            No students enrolled in this section.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
