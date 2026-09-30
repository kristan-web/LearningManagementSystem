{{-- Shared: Calendar --}}
@extends('layouts.student')
@section('title', 'Calendar')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $label = 'mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $blankForm = ['event_id' => null, 'title' => '', 'description' => '', 'start_datetime' => '', 'end_datetime' => ''];
@endphp

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    {{-- FullCalendar ships light-theme-only CSS and never sets an explicit color on day
         numbers, so they inherit the app's dark-mode body text color while the grid keeps
         FullCalendar's hardcoded light background vars — causing white-on-white cells. --}}
    <style>
        /* Weekday header labels (Sun, Mon, ...) — dark text in light mode. */
        .fc .fc-col-header-cell-cushion {
            color: #0f172a; /* slate-900 */
        }
        /* In dark mode, both the weekday header labels and the date numbers
           need to flip light so they're visible against the dark grid background. */
        html.dark .fc .fc-col-header-cell-cushion,
        html.dark .fc .fc-daygrid-day-number {
            color: #f1f5f9; /* slate-100 */
        }
        html.dark .fc {
            --fc-border-color: #334155;          /* slate-700 */
            --fc-page-bg-color: #1e293b;         /* slate-800 */
            --fc-neutral-bg-color: #334155;      /* slate-700 */
            --fc-neutral-text-color: #cbd5e1;    /* slate-300 */
            --fc-today-bg-color: rgb(37 99 235 / 0.15); /* blue-600 tint */
        }
    </style>
@endsection

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8"
     x-data="{
        formOpen: false,
        mode: 'create',
        form: @js($blankForm),
        blank: @js($blankForm),
        formError: null,
        deleteOpen: false,
        eventsUrl: @js(route('calendar.events')),
        storeUrl: @js(route('calendar.events.store')),
        updateUrl: @js(route('calendar.events.update', '__ID__')),
        destroyUrl: @js(route('calendar.events.destroy', '__ID__')),
        openCreate(start, end) {
            this.mode = 'create';
            this.form = { ...this.blank, start_datetime: start ?? '', end_datetime: end ?? start ?? '' };
            this.formOpen = true;
        },
        openEdit(info) {
            const props = info.event.extendedProps;
            if (props.source !== 'event' || !props.editable) return;
            this.mode = 'edit';
            this.form = {
                event_id: info.event.id.replace('event-', ''),
                title: info.event.title,
                description: props.description ?? '',
                start_datetime: info.event.startStr.slice(0, 16),
                end_datetime: (info.event.endStr || info.event.startStr).slice(0, 16),
            };
            this.formOpen = true;
        },
        openDelete() { this.deleteOpen = true; },
        get formAction() { return this.mode === 'edit' ? this.updateUrl.replace('__ID__', this.form.event_id) : this.storeUrl; }
     }"
     @keydown.escape.window="formOpen = false; deleteOpen = false"
     x-init="$nextTick(() => window.initLmsCalendar($refs.calendar, { eventsUrl: eventsUrl, onDateSelect: (s, e) => openCreate(s, e), onEventClick: (info) => openEdit(info) }))">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Calendar</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Assignment and quiz deadlines plus your personal events, all in one place.</p>
        </div>
        <button type="button" @click="openCreate()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:-translate-y-0.5 hover:shadow-blue-500/40 focus:outline-none focus-visible:ring-3 focus-visible:ring-blue-500/30">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Add Event
        </button>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 text-xs font-medium text-slate-600 dark:text-slate-300">
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Personal event</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Assignment due</span>
        <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span> Quiz due</span>
    </div>

    {{-- Calendar --}}
    <section class="{{ $card }} p-4">
        <div x-ref="calendar"></div>
    </section>

    {{-- Create / Edit modal --}}
    <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="calendar-form-title">
        <div x-show="formOpen" x-transition.opacity @click="formOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <form x-show="formOpen"
              x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
              @submit.prevent="window.submitLmsCalendarForm($data)"
              class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-start justify-between gap-4 border-b border-blue-100 px-6 py-4 dark:border-slate-700">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400" x-text="mode === 'edit' ? 'Edit Event' : 'New Event'"></p>
                    <h2 id="calendar-form-title" class="text-lg font-bold text-slate-900 dark:text-white">Personal Event</h2>
                </div>
                <button type="button" @click="formOpen = false" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">&times;</button>
            </div>

            <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5">
                <p x-show="formError" x-text="formError" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400"></p>
                <div>
                    <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                    <input id="title" name="title" type="text" x-model="form.title" required maxlength="255" class="{{ $input }}">
                </div>
                <div>
                    <label for="description" class="{{ $label }}">Description</label>
                    <textarea id="description" name="description" x-model="form.description" rows="3" class="{{ $input }}"></textarea>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="start_datetime" class="{{ $label }}">Starts <span class="text-red-500">*</span></label>
                        <input id="start_datetime" name="start_datetime" type="datetime-local" x-model="form.start_datetime" required class="{{ $input }}">
                    </div>
                    <div>
                        <label for="end_datetime" class="{{ $label }}">Ends <span class="text-red-500">*</span></label>
                        <input id="end_datetime" name="end_datetime" type="datetime-local" x-model="form.end_datetime" required class="{{ $input }}">
                    </div>
                </div>
                <p x-show="mode === 'edit'" class="text-xs text-slate-400 dark:text-slate-500">
                    <button type="button" class="text-red-600 hover:underline dark:text-red-400" @click="formOpen = false; openDelete()">Delete this event</button>
                </p>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-blue-100 bg-slate-50/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-slate-700 dark:bg-slate-900/40">
                <button type="button" @click="formOpen = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40" x-text="mode === 'edit' ? 'Save Changes' : 'Add Event'"></button>
            </div>
        </form>
    </div>

    {{-- Delete confirmation modal --}}
    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="calendar-delete-title">
        <div x-show="deleteOpen" x-transition.opacity @click="deleteOpen = false" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div x-show="deleteOpen" x-transition class="relative w-full max-w-md rounded-2xl border border-blue-100 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3M4 7h16"/></svg>
                </span>
                <div>
                    <h2 id="calendar-delete-title" class="text-lg font-bold text-slate-900 dark:text-white">Delete this event?</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-text="'\u201c' + form.title + '\u201d will be permanently removed.'"></p>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" @click="deleteOpen = false" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
                <button type="button" @click="window.deleteLmsCalendarEvent($data)" class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-500/25 transition hover:bg-red-700">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    @vite(['resources/js/calendar.js'])
@endsection

