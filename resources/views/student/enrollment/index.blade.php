{{-- Student: Enrollment (UI only: pass $enrollment, $student and $subjects from a controller to fill it) --}}
@extends('layouts.student')
@section('title', 'My Enrollment')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    // Expected data (all optional until this page has a controller):
    //   $student    -> App\Models\Student (student_number, lrn, grade_level, strand)
    //   $enrollment -> App\Models\Enrollment (school_year, semester, date_enrolled, status, section)
    //   $subjects   -> rows with subject_code, subject_name, schedule (e.g. "Mon/Wed 8:00 AM"), teacher_name
    $student = $student ?? null;
    $enrollment = $enrollment ?? null;
    $subjects = $subjects ?? collect();

    $status = $enrollment?->status ?? 'Not enrolled';
    $statusBadge = match ($status) {
        'Enrolled', 'Active' => 'bg-emerald-400/15 text-emerald-300 ring-emerald-400/30',
        'Pending' => 'bg-gold/15 text-gold ring-gold/30',
        default => 'bg-white/10 text-white/80 ring-white/20',
    };

    $details = [
        ['label' => 'Student Number', 'value' => $student?->student_number],
        ['label' => 'LRN', 'value' => $student?->lrn],
        ['label' => 'Grade Level', 'value' => $student?->grade_level ? 'Grade ' . $student->grade_level : null],
        ['label' => 'Strand', 'value' => $student?->strand?->strand_name],
        ['label' => 'Section', 'value' => $enrollment?->section?->section_name],
        ['label' => 'Date Enrolled', 'value' => $enrollment?->date_enrolled ? \Carbon\Carbon::parse($enrollment->date_enrolled)->format('M d, Y') : null],
    ];
@endphp

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">My Enrollment</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Your enrollment status and subject load.</p>
        </div>

        {{-- Status banner --}}
        <div class="mx-auto w-full max-w-6xl px-3 pt-3">
            <section class="bg-navy relative overflow-hidden rounded-2xl px-6 py-6 text-white shadow-[0_14px_32px_-14px_rgb(22_36_79/0.55)]">
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[1.6px] text-gold">Enrollment Status</p>
                        <h2 class="mt-1 font-display text-xl font-bold tracking-[-.3px]">School Year {{ $enrollment?->school_year ?? '—' }}</h2>
                        <p class="mt-1 text-sm text-white/70">{{ $enrollment?->semester ? $enrollment->semester . ' Semester' : 'Semester not set' }}</p>
                    </div>
                    <span class="inline-flex items-center gap-2 self-start rounded-full px-4 py-1.5 text-sm font-semibold ring-1 sm:self-center {{ $statusBadge }}">
                        <span class="h-2 w-2 rounded-full bg-current"></span>
                        {{ $status }}
                    </span>
                </div>
                <div class="pointer-events-none absolute -right-10 -top-12 h-44 w-44 rounded-full bg-white/5"></div>
            </section>
        </div>

        <div class="bento-grid">
            {{-- Details --}}
            <article class="bento-card bento-card--full">
                <div class="flex items-start gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 1 1 4 0v1m-4 0a2 2 0 1 0 4 0m-5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 0 0-2.83 2M15 11h3m-3 4h2"/></svg></span>
                    <div>
                        <div class="bento-card__label">Records</div>
                        <h2 class="bento-card__title">Enrollment Details</h2>
                        <p class="bento-card__description">Current school year, grade level, and status</p>
                    </div>
                </div>
                <dl class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($details as $detail)
                        <div class="rounded-xl border border-[#e3e8f4] bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900/45">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-ink/50 dark:text-slate-400">{{ $detail['label'] }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-ink dark:text-white">{{ $detail['value'] ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </article>

            {{-- Subject load --}}
            <article class="bento-card bento-card--full">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg></span>
                        <div>
                            <div class="bento-card__label">Academics</div>
                            <h2 class="bento-card__title">Subject Load</h2>
                            <p class="bento-card__description">Subjects you are enrolled in this term</p>
                        </div>
                    </div>
                    <a href="{{ route('student.schedule.index') }}" class="st-btn-outline btn-sm">View schedule</a>
                </div>

                <div class="st-table-wrap mt-3">
                    <table class="st-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject</th>
                                <th>Schedule</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subjects as $subject)
                                <tr>
                                    <td class="whitespace-nowrap"><span class="inline-flex rounded-full bg-[#eef3fd] px-2.5 py-0.5 text-xs font-semibold text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">{{ $subject->subject_code }}</span></td>
                                    <td class="font-semibold text-ink dark:text-white">{{ $subject->subject_name }}</td>
                                    <td>{{ $subject->schedule ?: '—' }}</td>
                                    <td>{{ $subject->teacher_name ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="st-table__empty">
                                        No subjects listed yet. Your subject load will appear here once your enrollment is processed.
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
