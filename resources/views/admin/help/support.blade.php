{{-- Admin: Support (contact channels + request form posting to SupportRequestController@store) --}}
@extends('layouts.admin')
@section('title', 'Support')

@php
    $user = auth()->user();

    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $cardHead = 'flex items-start gap-3 border-b border-blue-100 px-6 py-4 dark:border-slate-700';
    $cardIcon = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-brand dark:bg-blue-500/15 dark:text-blue-300';
    $cardTitle = 'text-base font-semibold text-ink dark:text-white';
    $cardSub = 'mt-0.5 text-xs text-ink/60 dark:text-slate-400';
    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $readonly = 'block w-full cursor-not-allowed rounded-lg border border-dashed border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-ink/60 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-400';
    $hint = 'mt-1 text-xs text-ink/60 dark:text-slate-400';
    $req = 'text-red-500';

    $channels = [
        ['title' => 'Email', 'value' => 'support@school.edu', 'href' => 'mailto:support@school.edu', 'note' => 'Replies within 1 business day',
         'icon' => 'M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z'],
        ['title' => 'Phone', 'value' => '(02) 8123 4567', 'href' => 'tel:+63281234567', 'note' => 'Mon–Fri, 8:00 AM – 5:00 PM',
         'icon' => 'M3 5a2 2 0 0 1 2-2h3.28a1 1 0 0 1 .948.684l1.498 4.493a1 1 0 0 1-.502 1.21l-2.257 1.13a11.042 11.042 0 0 0 5.516 5.516l1.13-2.257a1 1 0 0 1 1.21-.502l4.493 1.498a1 1 0 0 1 .684.949V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5Z'],
        ['title' => 'IT Office', 'value' => 'Admin Building, Room 104', 'href' => null, 'note' => 'Walk-ins welcome during office hours',
         'icon' => 'M17.657 16.657 13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0ZM15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    ];

    $categories = ['Account & Access', 'User Management', 'Curriculum & Subjects', 'Enrollment', 'Reports', 'Bug Report', 'Other'];
    $priorities = [
        'Low' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-500/15 dark:peer-checked:text-emerald-300',
        'Normal' => 'peer-checked:border-brand peer-checked:bg-blue-50 peer-checked:text-brand-deep dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-500/15 dark:peer-checked:text-blue-300',
        'High' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:border-amber-400 dark:peer-checked:bg-amber-500/15 dark:peer-checked:text-amber-300',
        'Urgent' => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:border-red-400 dark:peer-checked:bg-red-500/15 dark:peer-checked:text-red-300',
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-ink dark:text-white">Support</h1>
            <p class="text-sm text-ink/60 dark:text-slate-400">Get help from the IT team or send a support request.</p>
        </div>
        <a href="{{ route('admin.support.requests') }}" class="btn-navy">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5m16 0h-2.586a1 1 0 0 0-.707.293l-2.414 2.414a1 1 0 0 1-.707.293h-3.172a1 1 0 0 1-.707-.293l-2.414-2.414A1 1 0 0 0 6.586 13H4"/></svg>
            Support Requests
        </a>
    </div>

    {{-- Hero --}}
    <section class="bg-navy relative overflow-hidden rounded-2xl px-6 py-6 text-white shadow-[0_14px_32px_-14px_rgb(22_36_79/0.55)]">
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-lg font-bold">Need a quick answer?</h2>
                <p class="mt-1 text-sm text-white/70">Most questions are covered by the step-by-step guides.</p>
            </div>
            <a href="{{ route('admin.documentation') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13H7a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h12M9 3v14m7 0v4"/></svg>
                Browse Documentation
            </a>
        </div>
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/5"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[3px] bg-linear-to-r from-transparent via-gold to-transparent opacity-70"></div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left column: contact channels --}}
        <div class="space-y-6 lg:col-span-1">
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 0 1 0 12.728m-12.728 0a9 9 0 0 1 0-12.728M15.536 8.464a5 5 0 0 1 0 7.072m-7.072 0a5 5 0 0 1 0-7.072M13 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">Contact Us</h3>
                        <p class="{{ $cardSub }}">Reach the IT team directly.</p>
                    </div>
                </div>
                <ul class="space-y-1 p-3">
                    @foreach ($channels as $channel)
                        <li>
                            <a @if ($channel['href']) href="{{ $channel['href'] }}" @endif class="flex items-start gap-3 rounded-xl px-3 py-3 transition {{ $channel['href'] ? 'hover:bg-white hover:shadow-sm dark:hover:bg-white/5' : '' }}">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-brand ring-1 ring-blue-100 dark:bg-slate-900 dark:text-blue-300 dark:ring-slate-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $channel['icon'] }}"/></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-xs font-medium text-ink/60 dark:text-slate-400">{{ $channel['title'] }}</span>
                                    <span class="block truncate text-sm font-semibold text-ink dark:text-white">{{ $channel['value'] }}</span>
                                    <span class="block text-xs text-ink/60 dark:text-slate-400">{{ $channel['note'] }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- System status --}}
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">System Status</h3>
                        <p class="{{ $cardSub }}">Current service health.</p>
                    </div>
                </div>
                <ul class="space-y-3 px-6 py-5">
                    @foreach (['Web Portal', 'Database', 'Email Notifications'] as $service)
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-ink/80 dark:text-slate-300">{{ $service }}</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Operational
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Right column: support request form --}}
        <div class="lg:col-span-2">
            <section class="{{ $card }}" x-data="{ message: @js(old('message', '')) }">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5l-5 5v-5Z"/></svg>
                    </span>
                    <div>
                        <h3 class="{{ $cardTitle }}">Submit a Request</h3>
                        <p class="{{ $cardSub }}">Describe the problem and we will get back to you by email.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('support-requests.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 px-6 py-5 sm:grid-cols-2"
                      data-confirm="Send this request?" data-confirm-text="It will appear under Support Requests." data-confirm-button="Send request">
                    @csrf
                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:col-span-2 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label for="support_name" class="{{ $label }}">Name</label>
                        <input type="text" id="support_name" value="{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->name }}" class="{{ $readonly }}" readonly>
                    </div>
                    <div>
                        <label for="support_email" class="{{ $label }}">Reply-to Email</label>
                        <input type="email" id="support_email" value="{{ $user->email }}" class="{{ $readonly }}" readonly>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="category" class="{{ $label }}">Category <span class="{{ $req }}">*</span></label>
                        <select id="category" name="category" required class="{{ $input }}">
                            <option value="" disabled @selected(! old('category'))>Select a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="{{ $label }}">Priority</span>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($priorities as $priority => $checked)
                                <label class="cursor-pointer">
                                    <input type="radio" name="priority" value="{{ $priority }}" class="peer sr-only" @checked(old('priority', 'Normal') === $priority)>
                                    <span class="flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-ink/70 transition hover:border-brand/40 peer-focus-visible:ring-3 peer-focus-visible:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 {{ $checked }}">{{ $priority }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="subject" class="{{ $label }}">Subject <span class="{{ $req }}">*</span></label>
                        <input type="text" id="subject" name="subject" maxlength="120" required class="{{ $input }}" placeholder="Short summary of the issue" value="{{ old('subject') }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="{{ $label }}">Message <span class="{{ $req }}">*</span></label>
                        <textarea id="message" name="message" rows="6" maxlength="2000" required x-model="message" class="{{ $input }} resize-none" placeholder="What happened? What did you expect? Include steps to reproduce if you can."></textarea>
                        <p class="{{ $hint }} flex justify-between">
                            <span>Include page names and any error messages.</span>
                            <span x-text="`${message.length}/2000`"></span>
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="attachment" class="{{ $label }}">Screenshot</label>
                        <input type="file" id="attachment" name="attachment" accept="image/*"
                               class="block w-full text-sm text-ink/60 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-deep hover:file:bg-blue-100 dark:text-slate-400 dark:file:bg-blue-500/15 dark:file:text-blue-300">
                        <p class="{{ $hint }}">Optional. PNG or JPG, up to 5 MB.</p>
                    </div>
                    <div class="flex flex-col-reverse gap-3 sm:col-span-2 sm:flex-row sm:justify-end">
                        <button type="reset" @click="message = ''" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-ink/80 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Clear</button>
                        <button type="submit" class="btn-navy">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2Zm0 0v-8"/></svg>
                            Send Request
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
