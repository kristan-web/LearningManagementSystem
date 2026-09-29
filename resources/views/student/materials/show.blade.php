{{-- Student: Learning Materials for a single subject --}}
@extends('layouts.student')
@section('title', $subject->subject_name . ' Materials')

@section('styles')
    @include('partials.styles.bento')
@endsection

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <a href="{{ route('student.materials.index') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Materials
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $subject->subject_name }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Access study resources for this subject.</p>
        </div>
        @php
            $initials = collect(explode(' ', $subject->subject_name))->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->join('');
            $hue = crc32($subject->subject_name) % 360;
        @endphp
        <div class="bento-grid">
            <article class="bento-card bento-card--span-2" style="--thumb-hue: {{ $hue }}">
                <div class="bento-card__thumb">{{ Str::upper($initials) }}</div>
                <div class="bento-card__label">Subject</div>
                <h2 class="bento-card__title">{{ $subject->subject_name }}</h2>
                <p class="bento-card__description">{{ $materials->count() }} {{ Str::plural('material', $materials->count()) }} available</p>

                <ul class="mt-3 divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($materials as $material)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $material->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Posted {{ $material->uploaded_at?->format('M d, Y') }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <a href="{{ route('materials.preview', $material->material_id) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-slate-500/10 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-500 hover:text-white dark:text-slate-300">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Preview
                                </a>
                                <a href="{{ route('materials.download', $material->material_id) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-500 hover:text-white dark:text-blue-300">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                    Download
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="py-2.5 text-sm text-gray-500 dark:text-gray-400">No materials published for this subject yet.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </div>
@endsection
