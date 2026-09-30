@php
    $layout = match (auth()->user()?->role) {
        'Admin' => 'layouts.admin',
        'Teacher' => 'layouts.teacher',
        'Student' => 'layouts.student',
        default => 'layouts.app',
    };
@endphp
@extends($layout)
@section('title', 'Announcements')
@section('content')
<div class="mx-auto w-full max-w-5xl space-y-6 pt-6" x-data="{ createOpen: false, deleteOpen: false, deleteAction: '', announcementTitle: '' }">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800 dark:border-emerald-800/40 dark:bg-emerald-950/30 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Announcements</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">School updates and class notices.</p>
        </div>
        @if (in_array(auth()->user()?->role, ['Admin', 'Teacher'], true))
            <button type="button" @click="createOpen = true" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                New Announcement
            </button>
        @endif
    </div>
    @include('shared.announcements.feed')
    @include('shared.announcements.modals')
</div>
@endsection

