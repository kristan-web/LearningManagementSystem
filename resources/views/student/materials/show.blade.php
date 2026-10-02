{{-- Student: Learning Materials for a single subject --}}
@extends('layouts.student')
@section('title', $subject->subject_name . ' Materials')

@section('styles')
    @include('partials.styles.bento')
@endsection

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <a href="{{ route('student.materials.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Materials
            </a>
            <h1 class="mt-2 font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">{{ $subject->subject_name }}</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Access study resources for this subject.</p>
        </div>
        @php
            $initials = collect(explode(' ', $subject->subject_name))->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->join('');
            $hue = crc32($subject->subject_name) % 360;
        @endphp
        <div class="bento-grid">
            <article class="bento-card bento-card--full" style="--thumb-hue: {{ $hue }}">
                <div class="flex items-center gap-3">
                    <div class="bento-card__thumb !mb-0">{{ Str::upper($initials) }}</div>
                    <div>
                        <div class="bento-card__label">Subject</div>
                        <h2 class="bento-card__title">{{ $subject->subject_name }}</h2>
                        <p class="bento-card__description">{{ $materials->count() }} {{ Str::plural('material', $materials->count()) }} available</p>
                    </div>
                </div>

                <ul class="mt-4 space-y-2">
                    @forelse ($materials as $material)
                        <li class="flex flex-col gap-3 rounded-xl border border-[#e3e8f4] bg-white px-4 py-3 transition hover:border-[#c9d6f5] hover:bg-[#f5f7fd] sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-900/45 dark:hover:border-blue-400/40 dark:hover:bg-blue-500/10">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#eef3fd] text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink dark:text-white">{{ $material->title }}</p>
                                    <p class="text-xs text-ink/55 dark:text-slate-400">Posted {{ $material->uploaded_at?->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="{{ route('materials.preview', $material->material_id) }}" target="_blank" rel="noopener" class="st-btn-outline btn-sm">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Preview
                                </a>
                                <a href="{{ route('materials.download', $material->material_id) }}" class="btn-navy btn-sm">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                    Download
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-[#d5dcec] px-4 py-8 text-center text-sm text-ink/55 dark:border-slate-700 dark:text-slate-400">No materials published for this subject yet.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </div>
@endsection
