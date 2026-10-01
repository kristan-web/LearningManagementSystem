{{--
    Partial: Help documentation page body (student + teacher).
    Params: $sections (id,title,summary,icon,route,steps,tips), $faqs (q,a), $supportRoute, $portalName, $roleWord
--}}
@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $cardHead = 'flex items-start gap-3 border-b border-blue-100 px-6 py-4 dark:border-slate-700';
    $cardIcon = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-brand dark:bg-blue-500/15 dark:text-blue-300';
    $cardTitle = 'text-base font-semibold text-ink dark:text-white';
    $cardSub = 'mt-0.5 text-xs text-ink/60 dark:text-slate-400';
    $step = 'flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-semibold text-white dark:bg-blue-500';

@endphp
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">

    {{-- Page header --}}
    <div class="flex flex-col gap-1">
        <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Documentation</h1>
        <p class="text-sm text-ink/60 dark:text-slate-400">Step-by-step guides for every part of the {{ $portalName }}.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- Table of contents --}}
        <aside class="lg:col-span-1">
            <nav class="{{ $card }} p-4 lg:sticky lg:top-20" aria-label="Documentation sections">
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
                    <a href="{{ route($supportRoute) }}" class="font-semibold text-brand-deep hover:underline dark:text-blue-300">Contact support →</a>
                </div>
            </nav>
        </aside>

        {{-- Guides --}}
        <div class="space-y-6 lg:col-span-3">
            @foreach ($sections as $section)
                <section id="{{ $section['id'] }}" class="{{ $card }} scroll-mt-20">
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

            {{-- FAQ (native <details>, no script) --}}
            <section id="faq" class="{{ $card }} scroll-mt-20">
                <div class="{{ $cardHead }}">
                    <span class="{{ $cardIcon }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m.08 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <h2 class="{{ $cardTitle }}">Frequently Asked Questions</h2>
                        <p class="{{ $cardSub }}">Quick answers to common {{ $roleWord }} questions.</p>
                    </div>
                </div>
                <div class="divide-y divide-blue-100 px-6 dark:divide-slate-700">
                    @foreach ($faqs as $faq)
                        <details class="group py-1">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-3 text-sm font-medium text-ink transition hover:text-brand-deep dark:text-slate-200 dark:hover:text-white [&::-webkit-details-marker]:hidden">
                                {{ $faq['q'] }}
                                <svg class="h-4 w-4 shrink-0 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                            </summary>
                            <p class="pb-3 text-sm text-ink/70 dark:text-slate-400">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</div>
