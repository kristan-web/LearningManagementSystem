{{-- Admin: Documentation (static guide to every admin module, with client-side search) --}}
@extends('layouts.admin')
@section('title', 'Documentation')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $cardHead = 'flex items-start gap-3 border-b border-blue-100 px-6 py-4 dark:border-slate-700';
    $cardIcon = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-brand dark:bg-blue-500/15 dark:text-blue-300';
    $cardTitle = 'text-base font-semibold text-ink dark:text-white';
    $cardSub = 'mt-0.5 text-xs text-ink/60 dark:text-slate-400';
    $step = 'flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-semibold text-white dark:bg-blue-500';

    // Each section: id, title, summary, icon path, link, steps, tips.
    $sections = [
        [
            'id' => 'getting-started',
            'title' => 'Getting Started',
            'summary' => 'Find your way around the admin panel.',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7Z',
            'route' => route('admin.dashboard'),
            'steps' => [
                'Sign in with an account whose role is Admin.',
                'Use the sidebar to switch between modules. Click the round button on the sidebar edge to collapse or expand it.',
                'Toggle light or dark mode with the moon/sun button beside your name at the bottom of the sidebar.',
                'The Dashboard gives a quick summary of users, subjects and recent activity.',
            ],
            'tips' => ['The sidebar remembers whether you left it collapsed.'],
        ],
        [
            'id' => 'users',
            'title' => 'User Management',
            'summary' => 'Create, edit, deactivate and restore accounts.',
            'icon' => 'M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
            'route' => route('admin.users.index'),
            'steps' => [
                'Open User Management and click Add User.',
                'Fill in the name, email and role (Admin, Staff, Registrar, Accounting, Teacher, Student or Guardian).',
                'Set a temporary password of at least 8 characters and save.',
                'To change an account, open it from the list and click Edit.',
                'Deleting a user is a soft delete. Use Restore to bring the account back.',
            ],
            'tips' => ['Set status to Suspended or Locked to block sign-in without deleting the record.'],
        ],
        [
            'id' => 'academic',
            'title' => 'Academic & Curriculum',
            'summary' => 'Manage subjects, school years, terms and enrollment.',
            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
            'route' => route('admin.curriculum.index'),
            'steps' => [
                'Open Academic → Curriculum & Subjects.',
                'Add a subject with its code, name and details, then save.',
                'Edit or delete a subject from the actions on its row.',
                'Set up the active school year and terms under School Year & Terms.',
                'Enroll students into sections and subjects under Enrollment.',
            ],
            'tips' => ['Create the school year and terms before you start enrollment.'],
        ],
        [
            'id' => 'reports',
            'title' => 'Reports & Analytics',
            'summary' => 'Review system-wide numbers and trends.',
            'icon' => 'M3 3v18h18M7 16l4-4 4 4 5-6m-5 0h5v5',
            'route' => route('admin.reports.index'),
            'steps' => [
                'Open Reports & Analytics from the sidebar.',
                'Review the summary cards for totals by role and status.',
                'Use the charts to spot trends over time.',
            ],
            'tips' => ['Numbers reflect live data every time the page loads.'],
        ],
        [
            'id' => 'settings',
            'title' => 'Account Settings',
            'summary' => 'Update your own profile and password.',
            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'route' => route('admin.settings'),
            'steps' => [
                'Open Settings from the sidebar.',
                'Edit your personal and contact details.',
                'Enter a new password only if you want to change it. Leave it blank to keep the current one.',
                'Click Save Changes.',
            ],
            'tips' => ['Changing your own role or status can remove your admin access.'],
        ],
    ];

    $faqs = [
        ['q' => 'A user forgot their password. What do I do?', 'a' => 'Ask them to use "Forgot password" on the sign-in page, or open their account in User Management and set a new temporary password.'],
        ['q' => 'Why can a user not sign in?', 'a' => 'Check that the account status is Active and the account is not deleted. Suspended and Locked accounts cannot sign in.'],
        ['q' => 'Can I undo a deleted user?', 'a' => 'Yes. Deletion is a soft delete. Open the user and click Restore.'],
        ['q' => 'Can I delete a subject that is already in use?', 'a' => 'Remove or reassign enrollments that use the subject first, then delete it.'],
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8"
     x-data="{
        q: '',
        match(el) { return this.q.trim() === '' || el.textContent.toLowerCase().includes(this.q.trim().toLowerCase()) },
        get empty() { return this.q.trim() !== '' && ![...this.$root.querySelectorAll('[data-doc]')].some(el => this.match(el)) }
     }">

    {{-- Page header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-ink dark:text-white">Documentation</h1>
            <p class="text-sm text-ink/60 dark:text-slate-400">Step-by-step guides for every part of the admin panel.</p>
        </div>
        <div class="relative w-full sm:w-72">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
            <input type="search" x-model="q" placeholder="Search the docs…" aria-label="Search the documentation"
                   class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- Table of contents --}}
        <aside class="lg:col-span-1">
            <nav class="{{ $card }} p-4 lg:sticky lg:top-8" aria-label="Documentation sections">
                <p class="mb-2 px-2 text-[11px] font-semibold uppercase tracking-wider text-ink/45 dark:text-slate-500">On this page</p>
                <ul class="space-y-0.5">
                    @foreach ($sections as $section)
                        <li>
                            <a href="#{{ $section['id'] }}" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-ink/70 transition hover:bg-white hover:text-brand-deep dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"/></svg>
                                {{ $section['title'] }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="#faq" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-ink/70 transition hover:bg-white hover:text-brand-deep dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m.08 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            FAQ
                        </a>
                    </li>
                </ul>
                <div class="mt-4 rounded-xl bg-blue-50 p-3 text-xs text-ink/70 dark:bg-blue-500/10 dark:text-slate-300">
                    Can't find an answer?
                    <a href="{{ route('admin.support') }}" class="font-semibold text-brand-deep hover:underline dark:text-blue-300">Contact support →</a>
                </div>
            </nav>
        </aside>

        {{-- Guides --}}
        <div class="space-y-6 lg:col-span-3">
            @foreach ($sections as $section)
                <section id="{{ $section['id'] }}" data-doc x-show="match($el)" class="{{ $card }} scroll-mt-8">
                    <div class="{{ $cardHead }}">
                        <span class="{{ $cardIcon }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"/></svg>
                        </span>
                        <div class="flex-1">
                            <h2 class="{{ $cardTitle }}">{{ $section['title'] }}</h2>
                            <p class="{{ $cardSub }}">{{ $section['summary'] }}</p>
                        </div>
                        <a href="{{ $section['route'] }}" class="hidden shrink-0 items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-brand-deep transition hover:bg-blue-50 sm:inline-flex dark:text-blue-300 dark:hover:bg-blue-500/10">
                            Open
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg>
                        </a>
                    </div>
                    <div class="space-y-4 px-6 py-5">
                        <ol class="space-y-3">
                            @foreach ($section['steps'] as $i => $text)
                                <li class="flex items-start gap-3 text-sm text-ink/80 dark:text-slate-300">
                                    <span class="{{ $step }}">{{ $i + 1 }}</span>
                                    <span class="pt-0.5">{{ $text }}</span>
                                </li>
                            @endforeach
                        </ol>
                        @foreach ($section['tips'] as $tip)
                            <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                <svg class="mt-px h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636-.707.707M21 12h-1M4 12H3m3.343-5.657-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547Z"/></svg>
                                <span><span class="font-semibold">Tip:</span> {{ $tip }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach

            {{-- FAQ --}}
            <section id="faq" class="{{ $card }} scroll-mt-8" x-show="[...$el.querySelectorAll('[data-doc]')].some(el => match(el))">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m.08 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <h2 class="{{ $cardTitle }}">Frequently Asked Questions</h2>
                        <p class="{{ $cardSub }}">Quick answers to common admin questions.</p>
                    </div>
                </div>
                <div class="divide-y divide-blue-100 px-6 dark:divide-slate-700">
                    @foreach ($faqs as $faq)
                        <div data-doc x-show="match($el)" x-data="{ open: false }" class="py-1">
                            <button type="button" @click="open = !open" :aria-expanded="open"
                                    class="flex w-full items-center justify-between gap-4 py-3 text-left text-sm font-medium text-ink transition hover:text-brand-deep dark:text-slate-200 dark:hover:text-white">
                                {{ $faq['q'] }}
                                <svg class="h-4 w-4 shrink-0 transition-transform duration-300" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                            </button>
                            <div class="grid transition-[grid-template-rows] duration-300 ease-in-out" :class="open || q.trim() !== '' ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                                <p class="overflow-hidden text-sm text-ink/70 dark:text-slate-400"><span class="block pb-3">{{ $faq['a'] }}</span></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- No results --}}
            <div x-show="empty" x-cloak class="{{ $card }} flex flex-col items-center px-6 py-12 text-center">
                <span class="{{ $cardIcon }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
                </span>
                <p class="mt-3 text-sm font-semibold text-ink dark:text-white">No results for "<span x-text="q"></span>"</p>
                <p class="mt-1 text-xs text-ink/60 dark:text-slate-400">Try another word, or <a href="{{ route('admin.support') }}" class="font-semibold text-brand-deep hover:underline dark:text-blue-300">ask support</a>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
