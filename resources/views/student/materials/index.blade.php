{{-- Student: Learning Materials --}}
@extends('layouts.student')
@section('title', 'Learning Materials')

@section('styles')
    @include('partials.styles.bento')
@endsection

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Learning Materials</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Pick a subject to view its study resources.</p>
        </div>
        <div class="bento-grid">
            @forelse ($subjects as $entry)
                @php
                    $initials = collect(explode(' ', $entry->subject->subject_name))->map(fn ($w) => Str::substr($w, 0, 1))->take(2)->join('');
                    $hue = crc32($entry->subject->subject_name) % 360;
                @endphp
                <a href="{{ route('student.materials.show', $entry->subject->subject_id) }}" class="bento-card" style="--thumb-hue: {{ $hue }}">
                    <div class="bento-card__thumb">{{ Str::upper($initials) }}</div>
                    <div class="bento-card__label">Subject</div>
                    <h2 class="bento-card__title">{{ $entry->subject->subject_name }}</h2>
                    <p class="bento-card__description">{{ $entry->count }} {{ Str::plural('material', $entry->count) }} available</p>
                    <span class="mt-auto inline-flex items-center gap-1 pt-4 text-xs font-semibold text-brand-deep dark:text-blue-300">
                        Open materials
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg>
                    </span>
                </a>
            @empty
                <article class="bento-card bento-card--full items-center !py-10 text-center">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg></span>
                    <div class="bento-card__label mt-3">LMS Content</div>
                    <h2 class="bento-card__title">No materials yet</h2>
                    <p class="bento-card__description">Your teachers haven't published any learning materials for your section yet.</p>
                </article>
            @endforelse
        </div>
    </div>
@endsection
