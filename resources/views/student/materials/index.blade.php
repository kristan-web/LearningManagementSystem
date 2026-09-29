{{-- Student: Learning Materials --}}
@extends('layouts.student')
@section('title', 'Learning Materials')

@section('styles')
    @include('partials.styles.bento')
@endsection

@section('content')
    <div class="bento-content">
        <div class="bento-header">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Learning Materials</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pick a subject to view its study resources.</p>
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
                </a>
            @empty
                <article class="bento-card bento-card--span-2">
                    <div class="bento-card__label">LMS Content</div>
                    <h2 class="bento-card__title">No materials yet</h2>
                    <p class="bento-card__description">Your teachers haven't published any learning materials for your section yet.</p>
                </article>
            @endforelse
        </div>
    </div>
@endsection

