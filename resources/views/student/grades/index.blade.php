{{-- Student: Grades (StudentGradeController@index) — official final grades saved by teachers --}}
@extends('layouts.student')
@section('title', 'My Grades')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $grades = $grades ?? collect();
    $summary = $summary ?? [];
    $terms = $terms ?? collect();
    $gradeFormat = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');

    $remarksBadge = [
        'Passed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Failed' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        'Incomplete' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    ];

    $stats = [
        ['label' => 'General Average', 'value' => isset($summary['average']) ? number_format($summary['average'], 2) : '—', 'description' => 'Across all graded subjects', 'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z'],
        ['label' => 'Subjects', 'value' => $summary['subjects'] ?? '—', 'description' => 'With a final grade this term', 'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
        ['label' => 'Passed', 'value' => $summary['passed'] ?? '—', 'description' => 'Subjects with a passing grade', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ];

    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:focus:border-blue-400';
@endphp

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Grades</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Your grade report by subject and term.</p>
        </div>

        {{-- Summary --}}
        <div class="bento-grid">
            @foreach ($stats as $stat)
                <article class="bento-card !min-h-0">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="bento-card__label">{{ $stat['label'] }}</div>
                            <p class="bento-card__description mt-1">{{ $stat['description'] }}</p>
                        </div>
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg></span>
                    </div>
                    <div class="bento-card__value">{{ $stat['value'] }}</div>
                </article>
            @endforeach
        </div>

        <div class="bento-grid">
            <article class="bento-card bento-card--full">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg></span>
                        <div>
                            <div class="bento-card__label">Academics</div>
                            <h2 class="bento-card__title">Grade Report</h2>
                            <p class="bento-card__description">Grades across your enrolled subjects</p>
                        </div>
                    </div>

                    {{-- Term filter: one option per enrollment --}}
                    @if ($terms->count() > 1)
                        <form method="GET" action="{{ route('student.grades.index') }}" class="flex gap-2">
                            <label class="sr-only" for="grade-term">Term</label>
                            <select id="grade-term" name="term" class="{{ $input }} sm:w-72">
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}" @selected((int) $selected?->enrollment_id === $term->id)>{{ $term->label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-navy">View</button>
                        </form>
                    @elseif ($terms->isNotEmpty())
                        <p class="text-sm font-medium text-ink/60 dark:text-slate-400">{{ $terms->first()->label }}</p>
                    @endif
                </div>

                <div class="st-table-wrap mt-3">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th class="text-center">Final Grade</th>
                                <th>Remarks</th>
                                <th>Recorded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grades as $grade)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-ink dark:text-white">{{ $grade->subject_name }}</p>
                                        <p class="text-xs text-ink/50 dark:text-slate-500">{{ $grade->subject_code }}</p>
                                    </td>
                                    <td>{{ $grade->teacher_name ?: '—' }}</td>
                                    <td class="text-center font-display text-base font-bold text-ink dark:text-white">{{ $grade->final_grade !== null ? $gradeFormat($grade->final_grade) : '—' }}</td>
                                    <td>
                                        @if ($grade->remarks)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $remarksBadge[$grade->remarks] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300' }}">{{ $grade->remarks }}</span>
                                        @else
                                            <span class="text-ink/40 dark:text-slate-500">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-ink/60 dark:text-slate-400">{{ $grade->recorded_at?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="st-table__empty">
                                        No grades posted yet. Your grades will appear here once your teachers release them.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-xs text-ink/50 dark:text-slate-500">Passing grade is {{ \App\Models\FinalGrade::PASSING }}. Grades appear here once your teachers save them.</p>
            </article>
        </div>
    </div>
@endsection
