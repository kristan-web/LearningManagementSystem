{{--
    Partial: My Profile page body (student + teacher). Posts to profile.update with the same fields as before:
    birthdate, gender, contact_number, address, password, password_confirmation (+ specialization for teachers).
    Params:
      $roleLabel          'Student' | 'Teacher'
      $facts              [['label','value','icon'], ...]   strip under the header
      $record             [['label','value'], ...]          read-only school record
      $showSpecialization bool, $specialization string|null
      $cancelRoute, $supportRoute
      $passwordToggle     bool (show/hide password button)
    Expects $user and $errors from the page.
--}}
@php
    $label = 'mb-1.5 block text-sm font-semibold text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-xl border border-[#d5dcec] bg-white px-3.5 py-2.5 text-sm text-ink placeholder-slate-400 shadow-[0_1px_2px_rgb(22_36_79/0.05)] transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/15 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $withIcon = 'pl-10';
    $fieldIcon = 'pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/35 dark:text-slate-500';
    $hint = 'mt-1.5 text-xs text-ink/50 dark:text-slate-400';
    $error = 'mt-1.5 text-xs font-medium text-red-600 dark:text-red-400';
    $sectionTitle = 'font-display text-base font-bold text-ink dark:text-white';
    $sectionText = 'mt-1 text-xs leading-relaxed text-ink/55 dark:text-slate-400';

    $displayName = trim($user->first_name . ' ' . $user->last_name);
    $initials = mb_strtoupper(mb_substr($user->first_name ?? '', 0, 1) . mb_substr($user->last_name ?? '', 0, 1));
    $active = $user->status === 'Active';

    // Profile completeness: which editable details are filled in (saved values).
    $checklist = array_values(array_filter([
        $showSpecialization ? ['label' => 'Specialization', 'done' => filled($specialization), 'anchor' => '#specialization'] : null,
        ['label' => 'Birthdate', 'done' => filled($user->birthdate), 'anchor' => '#birthdate'],
        ['label' => 'Gender', 'done' => filled($user->gender), 'anchor' => '#personal'],
        ['label' => 'Contact number', 'done' => filled($user->contact_number), 'anchor' => '#contact_number'],
        ['label' => 'Address', 'done' => filled($user->address), 'anchor' => '#address'],
    ]));
    $doneCount = collect($checklist)->where('done', true)->count();
    $percent = (int) round($doneCount / max(count($checklist), 1) * 100);
    $ring = 2 * M_PI * 26; // circumference of the progress ring (r = 26)

    $sections = [
        ['id' => 'personal', 'label' => 'Personal', 'icon' => 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z'],
        ['id' => 'contact', 'label' => 'Contact', 'icon' => 'M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z'],
        ['id' => 'security', 'label' => 'Password', 'icon' => 'M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z'],
    ];
@endphp

<div class="mx-auto w-full max-w-6xl space-y-6 pb-10 pt-6">

    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>Some details need fixing. Check the highlighted fields below.</span>
        </div>
    @endif

    {{-- ===== Profile header ===== --}}
    <section class="relative overflow-hidden rounded-3xl border border-[#e3e8f4] bg-white shadow-[0_1px_2px_rgb(22_36_79/0.04),0_18px_40px_-20px_rgb(22_36_79/0.35)] dark:border-[#24386f] dark:bg-[#0f1a38] dark:shadow-none">
        {{-- Cover --}}
        <div class="bg-navy relative h-32 overflow-hidden sm:h-40">
            <div class="absolute inset-0 opacity-[.14] [background-image:radial-gradient(#fff_1px,transparent_1px)] [background-size:18px_18px]"></div>
            <div class="absolute -right-20 -top-28 h-80 w-80 rounded-full border-[36px] border-white/[.06]"></div>
            <div class="absolute -bottom-24 right-40 h-56 w-56 rounded-full bg-gold/10 blur-2xl"></div>
            <div class="absolute inset-x-0 bottom-0 h-[3px] bg-linear-to-r from-transparent via-gold to-transparent opacity-80"></div>
            <span class="absolute right-5 top-5 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[1.4px] text-white/85 ring-1 ring-white/15 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-gold"></span>
                {{ $roleLabel }} Portal
            </span>
        </div>

        <div class="relative px-5 pb-6 sm:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Avatar: only the avatar overlaps the cover, so the name always sits on white --}}
                <div class="relative -mt-12 w-fit shrink-0 sm:-mt-10">
                    <span class="flex h-24 w-24 items-center justify-center rounded-[1.75rem] bg-linear-150 from-[#3a52a0] to-ink font-display text-3xl font-bold text-white shadow-[0_12px_28px_-10px_rgb(22_36_79/0.7)] ring-[5px] ring-white sm:h-28 sm:w-28 sm:text-4xl dark:ring-[#0f1a38]">{{ $initials }}</span>
                    <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full ring-4 ring-white dark:ring-[#0f1a38] {{ $active ? 'bg-emerald-500' : 'bg-amber-400' }}" title="{{ $user->status }}"></span>
                </div>

                <div class="min-w-0 flex-1 sm:pb-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="truncate font-display text-2xl font-bold tracking-[-.5px] text-ink sm:text-[1.75rem] dark:text-white">{{ $displayName }}</h1>
                        <span class="inline-flex items-center rounded-full bg-gold/15 px-2.5 py-0.5 text-xs font-bold text-gold-deep dark:bg-gold/10 dark:text-gold">{{ $roleLabel }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $user->status }}
                        </span>
                    </div>
                    <p class="mt-1 flex items-center gap-1.5 truncate text-sm text-ink/60 dark:text-slate-400">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            {{-- Facts strip --}}
            {{-- gap-px over a tinted background draws the separators at any column count --}}
            <dl class="mt-6 grid grid-cols-1 gap-px overflow-hidden rounded-2xl bg-[#e3e8f4] ring-1 ring-[#e3e8f4] sm:grid-cols-2 lg:grid-cols-4 dark:bg-white/10 dark:ring-white/10">
                @foreach ($facts as $fact)
                    <div class="flex items-center gap-3 bg-[#f5f7fd] px-4 py-3.5 dark:bg-[#132150]">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand shadow-[0_1px_2px_rgb(22_36_79/0.08)] ring-1 ring-[#e3e8f4] dark:bg-white/5 dark:text-blue-300 dark:ring-white/10">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fact['icon'] }}"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-slate-400">{{ $fact['label'] }}</dt>
                            <dd class="truncate text-sm font-bold text-ink dark:text-white">{{ $fact['value'] ?: '—' }}</dd>
                        </div>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

        {{-- ===== Side column ===== --}}
        <aside class="order-2 space-y-4 lg:sticky lg:top-24 lg:order-none lg:col-span-4">
            {{-- Completeness --}}
            <section class="rounded-3xl border border-[#e3e8f4] bg-linear-to-b from-white to-[#f7f9fe] p-5 shadow-[0_1px_2px_rgb(22_36_79/0.04),0_8px_24px_-12px_rgb(22_36_79/0.16)] dark:border-[#24386f] dark:from-[#0f1a38] dark:to-[#0f1a38] dark:shadow-none">
                <div class="flex items-center gap-4">
                    <div class="relative h-16 w-16 shrink-0">
                        <svg class="h-16 w-16 -rotate-90" viewBox="0 0 60 60" aria-hidden="true">
                            <circle cx="30" cy="30" r="26" fill="none" stroke-width="6" class="stroke-[#e3e8f4] dark:stroke-white/10"/>
                            <circle cx="30" cy="30" r="26" fill="none" stroke-width="6" stroke-linecap="round"
                                    class="{{ $percent === 100 ? 'stroke-emerald-500' : 'stroke-brand' }}"
                                    stroke-dasharray="{{ $ring }}" stroke-dashoffset="{{ $ring * (1 - $percent / 100) }}"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center font-display text-sm font-bold text-ink dark:text-white">{{ $percent }}%</span>
                    </div>
                    <div>
                        <p class="font-display text-base font-bold text-ink dark:text-white">{{ $percent === 100 ? 'Profile complete' : 'Complete your profile' }}</p>
                        <p class="text-xs text-ink/55 dark:text-slate-400">{{ $doneCount }} of {{ count($checklist) }} details added</p>
                    </div>
                </div>
                <ul class="mt-4 space-y-1">
                    @foreach ($checklist as $item)
                        <li>
                            <a href="{{ $item['anchor'] }}" class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm transition hover:bg-white dark:hover:bg-white/5 {{ $item['done'] ? 'text-ink/55 dark:text-slate-400' : 'font-semibold text-ink dark:text-white' }}">
                                @if ($item['done'])
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @else
                                    <span class="h-5 w-5 shrink-0 rounded-full border-2 border-dashed border-ink/25 dark:border-white/25"></span>
                                @endif
                                {{ $item['done'] ? $item['label'] : 'Add your ' . mb_strtolower($item['label']) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Jump links --}}
            <nav class="hidden rounded-3xl border border-[#e3e8f4] bg-white/70 p-2 lg:block dark:border-[#24386f] dark:bg-white/5" aria-label="Profile sections">
                @foreach ($sections as $section)
                    <a href="#{{ $section['id'] }}" class="group flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold text-ink/70 transition hover:bg-white hover:text-ink hover:shadow-sm dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#eef3fd] text-brand transition group-hover:bg-linear-150 group-hover:from-[#3a52a0] group-hover:to-ink group-hover:text-white dark:bg-blue-500/15 dark:text-blue-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] }}"/></svg>
                        </span>
                        {{ $section['label'] }}
                        <svg class="ml-auto h-4 w-4 text-ink/30 transition group-hover:translate-x-0.5 group-hover:text-ink/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    </a>
                @endforeach
            </nav>

            {{-- Read-only record --}}
            <section class="rounded-3xl border border-[#e3e8f4] bg-white/70 p-5 dark:border-[#24386f] dark:bg-white/5">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-display text-sm font-bold text-ink dark:text-white">School record</h2>
                    <span class="inline-flex items-center gap-1 rounded-full bg-ink/5 px-2 py-0.5 text-[11px] font-semibold text-ink/55 dark:bg-white/10 dark:text-slate-400">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z"/></svg>
                        Read-only
                    </span>
                </div>
                <dl class="mt-3 space-y-2.5">
                    @foreach ($record as $row)
                        <div class="flex items-start justify-between gap-3 text-sm">
                            <dt class="shrink-0 text-ink/50 dark:text-slate-400">{{ $row['label'] }}</dt>
                            <dd class="min-w-0 break-words text-right font-semibold text-ink dark:text-white">{{ $row['value'] ?: '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
                <a href="{{ route($supportRoute) }}" class="mt-4 flex items-center justify-between rounded-xl bg-[#eef3fd] px-3 py-2.5 text-xs font-semibold text-brand-deep transition hover:bg-[#dbe5fb] dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                    Something wrong here? Request a correction
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/></svg>
                </a>
            </section>
        </aside>

        {{-- ===== Edit form ===== --}}
        <form method="POST" action="{{ route('profile.update') }}"
              class="relative order-1 rounded-3xl border border-[#e3e8f4] bg-white shadow-[0_1px_2px_rgb(22_36_79/0.04),0_8px_24px_-12px_rgb(22_36_79/0.16)] lg:order-none lg:col-span-8 dark:border-[#24386f] dark:bg-[#0f1a38] dark:shadow-none"
              data-confirm="Save your changes?" data-confirm-text="Your profile will be updated. If you entered a new password, use it the next time you sign in." data-confirm-button="Save changes">
            @csrf
            @method('PUT')

            {{-- Personal --}}
            <section id="personal" class="grid scroll-mt-24 grid-cols-1 gap-5 p-6 sm:p-8 md:grid-cols-[12rem_1fr]">
                <div>
                    <span class="st-icon !h-10 !w-10"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sections[0]['icon'] }}"/></svg></span>
                    <h2 class="{{ $sectionTitle }} mt-3">Personal details</h2>
                    <p class="{{ $sectionText }}">{{ $showSpecialization ? 'Your teaching specialization, birthdate and gender.' : 'Your birthdate and gender.' }}</p>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @if ($showSpecialization)
                        <div class="sm:col-span-2">
                            <label for="specialization" class="{{ $label }}">Specialization</label>
                            <div class="relative">
                                <svg class="{{ $fieldIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                                <input type="text" id="specialization" name="specialization" maxlength="255" value="{{ old('specialization', $specialization ?? '') }}" class="{{ $input }} {{ $withIcon }}" placeholder="e.g. Mathematics">
                            </div>
                            @error('specialization') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label for="birthdate" class="{{ $label }}">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}" class="{{ $input }}">
                        @error('birthdate') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <span class="{{ $label }}">Gender</span>
                        <div class="grid grid-cols-3 gap-1 rounded-xl bg-[#f0f3fa] p-1 dark:bg-white/5">
                            @foreach (['Male', 'Female', 'Other'] as $gender)
                                <label class="cursor-pointer">
                                    <input type="radio" name="gender" value="{{ $gender }}" class="peer sr-only" {{ old('gender', $user->gender) === $gender ? 'checked' : '' }}>
                                    <span class="flex items-center justify-center rounded-lg px-2 py-2 text-sm font-semibold text-ink/55 transition hover:text-ink peer-checked:bg-white peer-checked:text-ink peer-checked:shadow-[0_1px_3px_rgb(22_36_79/0.15)] peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40 dark:text-slate-400 dark:hover:text-white dark:peer-checked:bg-slate-700 dark:peer-checked:text-white">{{ $gender }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('gender') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="mx-6 border-t border-dashed border-[#e3e8f4] sm:mx-8 dark:border-white/10"></div>

            {{-- Contact --}}
            <section id="contact" class="grid scroll-mt-24 grid-cols-1 gap-5 p-6 sm:p-8 md:grid-cols-[12rem_1fr]">
                <div>
                    <span class="st-icon !h-10 !w-10"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sections[1]['icon'] }}"/></svg></span>
                    <h2 class="{{ $sectionTitle }} mt-3">Contact</h2>
                    <p class="{{ $sectionText }}">How the school can reach you.</p>
                </div>
                <div class="grid grid-cols-1 gap-5">
                    <div class="sm:max-w-xs">
                        <label for="contact_number" class="{{ $label }}">Contact number</label>
                        <div class="relative">
                            <svg class="{{ $fieldIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 0 1 2-2h3.28a1 1 0 0 1 .948.684l1.498 4.493a1 1 0 0 1-.502 1.21l-2.257 1.13a11.042 11.042 0 0 0 5.516 5.516l1.13-2.257a1 1 0 0 1 1.21-.502l4.493 1.498a1 1 0 0 1 .684.949V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5Z"/></svg>
                            <input type="tel" id="contact_number" name="contact_number" maxlength="20" value="{{ old('contact_number', $user->contact_number) }}" class="{{ $input }} {{ $withIcon }}" placeholder="09XX XXX XXXX">
                        </div>
                        @error('contact_number') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="address" class="{{ $label }}">Address</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-3 h-4 w-4 text-ink/35 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657 13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0ZM15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <textarea id="address" name="address" rows="3" maxlength="255" class="{{ $input }} {{ $withIcon }} resize-none" placeholder="Street, Barangay, City, Province">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <p class="{{ $hint }}">Up to 255 characters.</p>
                        @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="mx-6 border-t border-dashed border-[#e3e8f4] sm:mx-8 dark:border-white/10"></div>

            {{-- Password --}}
            <section id="security" class="grid scroll-mt-24 grid-cols-1 gap-5 p-6 sm:p-8 md:grid-cols-[12rem_1fr]" @if ($passwordToggle) x-data="{ show: false }" @endif>
                <div>
                    <span class="st-icon !h-10 !w-10"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sections[2]['icon'] }}"/></svg></span>
                    <h2 class="{{ $sectionTitle }} mt-3">Password</h2>
                    <p class="{{ $sectionText }}">Leave both fields blank to keep your current password.</p>
                </div>
                <div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="password" class="{{ $label }}">New password</label>
                            <div class="relative">
                                <input @if ($passwordToggle) :type="show ? 'text' : 'password'" @endif type="password" id="password" name="password" minlength="8" autocomplete="new-password" class="{{ $input }} {{ $passwordToggle ? 'pr-11' : '' }}" placeholder="At least 8 characters">
                                @if ($passwordToggle)
                                    <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'"
                                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-ink/40 transition hover:text-brand dark:text-slate-400 dark:hover:text-blue-300">
                                        <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
                                        <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 0 1 1.563-3.029m5.858.908a3 3 0 1 1 4.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532 3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0 1 12 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 0 1-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                @endif
                            </div>
                            @error('password') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="{{ $label }}">Confirm new password</label>
                            <input @if ($passwordToggle) :type="show ? 'text' : 'password'" @endif type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" class="{{ $input }}" placeholder="Repeat new password">
                        </div>
                    </div>
                    <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink/50 dark:text-slate-400">
                        <li class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-gold"></span>At least 8 characters</li>
                        <li class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-gold"></span>Both fields must match</li>
                        <li class="flex items-center gap-1.5"><span class="h-1 w-1 rounded-full bg-gold"></span>Never share it with anyone</li>
                    </ul>
                </div>
            </section>

            {{-- Save bar: stays at the bottom of the screen while the form is in view --}}
            <div class="sticky bottom-4 z-10 mx-3 mb-3 flex flex-col gap-3 rounded-2xl bg-ink/95 px-4 py-3 text-white shadow-[0_18px_40px_-12px_rgb(22_36_79/0.6)] ring-1 ring-white/10 backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:bg-slate-800/95">
                <p class="hidden items-center gap-2 text-sm text-white/75 sm:flex">
                    <svg class="h-4 w-4 shrink-0 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    Review your details, then save.
                </p>
                <div class="flex gap-2">
                    <a href="{{ route($cancelRoute) }}" class="inline-flex min-h-10 flex-1 items-center justify-center whitespace-nowrap rounded-xl px-4 text-sm font-semibold text-white/80 ring-1 ring-white/20 transition hover:bg-white/10 hover:text-white sm:flex-none">Cancel</a>
                    <button type="submit" class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-white px-5 text-sm font-bold text-ink shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Save changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
