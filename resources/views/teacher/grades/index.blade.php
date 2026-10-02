{{-- Teacher: Grades & Records (TeacherClassroomController@grades) — live gradebook from assignment and quiz scores --}}
@extends('layouts.teacher')
@section('title', 'Grades & Records')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $classLabel = fn ($s) => trim(($s->subject?->subject_name ?? '') . ' — ' . ($s->section?->section_name ?? '') . ' (' . Str::substr($s->day_of_week, 0, 3) . ' ' . \Carbon\Carbon::parse($s->start_time)->format('g:i A') . ')');
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:focus:border-blue-400';
    $averageTone = fn ($avg) => $avg === null ? 'text-ink/40 dark:text-slate-500' : ($avg >= 75 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400');
    $scoreFormat = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.');
    $passing = $rows->filter(fn ($r) => $r->average !== null && $r->average >= 75)->count();
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Grades &amp; Records</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Scores from every assignment and quiz in a class, with each student's running average.</p>
        </div>
        @if ($schedules->isNotEmpty())
            <form method="GET" action="{{ route('teacher.grades.index') }}" class="flex w-full gap-2 lg:w-auto">
                <label for="grade-class" class="sr-only">Class</label>
                <select id="grade-class" name="schedule_id" class="{{ $input }} lg:w-80" onchange="this.form.submit()">
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->schedule_id }}" @selected($selected?->schedule_id === $schedule->schedule_id)>{{ $classLabel($schedule) }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="btn-navy">View</button></noscript>
            </form>
        @endif
    </div>

    @if (! $selected)
        <div class="bento-grid">
            <article class="bento-card bento-card--full items-center !py-10 text-center">
                <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/></svg></span>
                <h2 class="bento-card__title mt-3">No classes yet</h2>
                <p class="bento-card__description">The gradebook fills in once you have a class schedule.</p>
            </article>
        </div>
    @else
        {{-- Summary --}}
        <div class="bento-grid !grid-cols-2 lg:!grid-cols-4">
            @foreach ([
                ['Students', $students->count(), 'Enrolled in this section'],
                ['Graded Items', $columns->count(), 'Assignments and quizzes'],
                ['Class Average', $classAverage !== null ? $classAverage . '%' : '—', 'Across students with scores'],
                ['Passing', $rows->isNotEmpty() ? $passing . ' / ' . $rows->count() : '—', 'Average of 75% or higher'],
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
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg></span>
                        <div>
                            <div class="bento-card__label">Gradebook</div>
                            <h2 class="bento-card__title">{{ $selected->subject?->subject_name }} — {{ $selected->section?->section_name }}</h2>
                            <p class="bento-card__description">Quiz scores use each student's best attempt. Ungraded submissions are marked "To grade".</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('teacher.assignments.index', ['schedule_id' => $selected->schedule_id]) }}" class="st-btn-outline btn-sm">Assignments</a>
                        <a href="{{ route('teacher.quizzes.index', ['schedule_id' => $selected->schedule_id]) }}" class="st-btn-outline btn-sm">Quizzes</a>
                    </div>
                </div>

                <div class="st-table-wrap mt-3">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                @foreach ($columns as $column)
                                    <th class="text-center" title="{{ $column->title }}">
                                        <span class="block text-[10px] {{ $column->type === 'Quiz' ? 'text-gold-deep' : 'text-brand' }}">{{ $column->type }}</span>
                                        <span class="block max-w-[8rem] truncate normal-case tracking-normal">{{ $column->title }}</span>
                                    </th>
                                @endforeach
                                <th class="text-center">Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="whitespace-nowrap font-semibold text-ink dark:text-white">{{ $row->student->user?->last_name }}, {{ $row->student->user?->first_name }}</td>
                                    @foreach ($columns as $column)
                                        @php $cell = $row->cells[$column->key]; @endphp
                                        <td class="whitespace-nowrap text-center">
                                            @if ($cell->score !== null)
                                                <span class="font-semibold text-ink dark:text-white">{{ $scoreFormat($cell->score) }}</span><span class="text-xs text-ink/45 dark:text-slate-500">{{ $column->type === 'Quiz' ? '%' : ' / ' . $scoreFormat($column->max) }}</span>
                                            @elseif ($cell->status === 'To grade')
                                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">To grade</span>
                                            @else
                                                <span class="text-ink/30 dark:text-slate-600">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="whitespace-nowrap text-center font-display text-base font-bold {{ $averageTone($row->average) }}">{{ $row->average !== null ? $row->average . '%' : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $columns->count() + 2 }}" class="st-table__empty">No students are enrolled in this section yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($columns->isEmpty() && $rows->isNotEmpty())
                    <p class="mt-2 text-xs text-ink/55 dark:text-slate-400">No assignments or quizzes in this class yet, so there is nothing to grade.</p>
                @endif
            </article>
        </div>

        {{-- Official final grades (TeacherClassroomController@saveFinalGrades) --}}
        @if ($rows->isNotEmpty())
            <div class="bento-grid">
                <article class="bento-card bento-card--full bento-card--static">
                    <div class="flex items-start gap-3">
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 0 0 1.946-.806 3.42 3.42 0 0 1 4.438 0 3.42 3.42 0 0 0 1.946.806 3.42 3.42 0 0 1 3.138 3.138 3.42 3.42 0 0 0 .806 1.946 3.42 3.42 0 0 1 0 4.438 3.42 3.42 0 0 0-.806 1.946 3.42 3.42 0 0 1-3.138 3.138 3.42 3.42 0 0 0-1.946.806 3.42 3.42 0 0 1-4.438 0 3.42 3.42 0 0 0-1.946-.806 3.42 3.42 0 0 1-3.138-3.138 3.42 3.42 0 0 0-.806-1.946 3.42 3.42 0 0 1 0-4.438 3.42 3.42 0 0 0 .806-1.946 3.42 3.42 0 0 1 3.138-3.138Z"/></svg></span>
                        <div>
                            <div class="bento-card__label">Official record</div>
                            <h2 class="bento-card__title">Final Grades</h2>
                            <p class="bento-card__description">Prefilled with each running average — adjust if needed, then save. Students see saved grades on their Grades page. Remarks on "Auto" become Passed at {{ \App\Models\FinalGrade::PASSING }} or higher, otherwise Failed. Leave a grade blank to skip that student.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('teacher.grades.final') }}" class="mt-3"
                          data-confirm="Save final grades?" data-confirm-text="Students will see these grades on their Grades page. Locked grades are not changed." data-confirm-button="Save grades">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $selected->schedule_id }}">
                        <div class="st-table-wrap">
                            <table class="st-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th class="text-center">Running Avg.</th>
                                        <th class="text-center">Final Grade</th>
                                        <th>Remarks</th>
                                        <th>Record</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        @php
                                            $sid = (int) $row->student->student_id;
                                            $saved = $finalGrades->get($sid);
                                            $ratingValue = old('final_rating.' . $sid, $saved ? $scoreFormat($saved->final_rating) : $row->average);
                                            $remarksValue = old('remarks.' . $sid, $saved?->remarks);
                                        @endphp
                                        <tr class="[&>td]:!align-middle">
                                            <td class="whitespace-nowrap font-semibold text-ink dark:text-white">{{ $row->student->user?->last_name }}, {{ $row->student->user?->first_name }}</td>
                                            <td class="whitespace-nowrap text-center {{ $averageTone($row->average) }}">{{ $row->average !== null ? $row->average . '%' : '—' }}</td>
                                            <td class="text-center">
                                                <label for="final-{{ $sid }}" class="sr-only">Final grade for {{ $row->student->user?->first_name }}</label>
                                                <input type="number" id="final-{{ $sid }}" name="final_rating[{{ $sid }}]" value="{{ $ratingValue }}" min="0" max="100" step="0.01"
                                                       class="{{ $input }} !w-24 !py-1.5 text-center" @disabled($saved?->is_locked)>
                                            </td>
                                            <td>
                                                <label for="remarks-{{ $sid }}" class="sr-only">Remarks for {{ $row->student->user?->first_name }}</label>
                                                <select id="remarks-{{ $sid }}" name="remarks[{{ $sid }}]" class="{{ $input }} !w-36 !py-1.5" @disabled($saved?->is_locked)>
                                                    <option value="">Auto</option>
                                                    @foreach (\App\Models\FinalGrade::REMARKS as $remark)
                                                        <option value="{{ $remark }}" @selected($remarksValue === $remark)>{{ $remark }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="whitespace-nowrap text-xs">
                                                @if ($saved?->is_locked)
                                                    <span class="inline-flex rounded-full bg-slate-200 px-2 py-0.5 font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-200">Locked</span>
                                                @elseif ($saved)
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Saved</span>
                                                    <span class="ml-1 text-ink/45 dark:text-slate-500">{{ $saved->computed_at?->format('M d, Y') }}</span>
                                                @else
                                                    <span class="text-ink/45 dark:text-slate-500">Not saved</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('final_rating.*') <p class="mt-2 text-xs font-medium text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <div class="mt-3 flex justify-end">
                            <button type="submit" class="btn-navy">Save Final Grades</button>
                        </div>
                    </form>
                </article>
            </div>
        @endif
    @endif
</div>
@endsection

