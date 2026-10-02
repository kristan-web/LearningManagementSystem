{{-- Admin: System Settings (account settings for the signed-in user, fields mirror the users table).
     Posts to admin.users.update (PUT) with the same fields as before: first_name, middle_name, last_name, birthdate,
     gender, email, contact_number, address, role, status, password. --}}
@extends('layouts.admin')
@section('title', 'System Settings')

@php
    $user = auth()->user();

    $label = 'mb-1.5 block text-sm font-semibold text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-xl border border-[#d5dcec] bg-white px-3.5 py-2.5 text-sm text-ink placeholder-slate-400 shadow-[0_1px_2px_rgb(22_36_79/0.05)] transition focus:border-brand focus:outline-none focus:ring-4 focus:ring-brand/15 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $withIcon = 'pl-10';
    $fieldIcon = 'pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/35 dark:text-slate-500';
    $hint = 'mt-1.5 text-xs text-ink/50 dark:text-slate-400';
    $error = 'mt-1.5 text-xs font-medium text-red-600 dark:text-red-400';
    $req = 'text-red-500';
    $panel = 'scroll-mt-40 rounded-3xl border border-[#e3e8f4] bg-white shadow-[0_1px_2px_rgb(22_36_79/0.04),0_8px_24px_-12px_rgb(22_36_79/0.16)] dark:border-[#24386f] dark:bg-[#0f1a38] dark:shadow-none';
    $panelHead = 'flex items-start gap-4 border-b border-[#eef1f8] px-6 py-5 sm:px-7 dark:border-white/10';
    $step = 'font-display text-3xl font-bold leading-none text-ink/10 dark:text-white/10';
    $panelTitle = 'font-display text-base font-bold text-ink dark:text-white';
    $panelText = 'mt-0.5 text-xs text-ink/55 dark:text-slate-400';

    $fullName = trim($user->first_name . ' ' . $user->last_name);
    $initials = mb_strtoupper(mb_substr($user->first_name ?? '', 0, 1) . mb_substr($user->last_name ?? '', 0, 1));
    $active = $user->status === 'Active';
    $userCode = 'ADM-' . str_pad((string) $user->user_id, 5, '0', STR_PAD_LEFT);

    $sections = [
        ['id' => 'identity', 'label' => 'Identity'],
        ['id' => 'contact', 'label' => 'Contact'],
        ['id' => 'access', 'label' => 'Access'],
        ['id' => 'security', 'label' => 'Security'],
    ];

    $statusStyles = [
        'Active' => ['dot' => 'bg-emerald-500', 'on' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 dark:peer-checked:border-emerald-400 dark:peer-checked:bg-emerald-500/15 dark:peer-checked:text-emerald-300'],
        'Inactive' => ['dot' => 'bg-slate-400', 'on' => 'peer-checked:border-slate-500 peer-checked:bg-slate-100 peer-checked:text-slate-700 dark:peer-checked:border-slate-400 dark:peer-checked:bg-slate-500/20 dark:peer-checked:text-slate-200'],
        'Suspended' => ['dot' => 'bg-amber-500', 'on' => 'peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 dark:peer-checked:border-amber-400 dark:peer-checked:bg-amber-500/15 dark:peer-checked:text-amber-300'],
        'Locked' => ['dot' => 'bg-red-500', 'on' => 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 dark:peer-checked:border-red-400 dark:peer-checked:bg-red-500/15 dark:peer-checked:text-red-300'],
    ];

    // Navigation shortcuts to the modules this account manages (links only).
    $modules = [
        ['label' => 'User Management', 'href' => route('admin.users.index'), 'icon' => 'M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
        ['label' => 'Curriculum & Subjects', 'href' => route('admin.curriculum.index'), 'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
        ['label' => 'Enrollment', 'href' => route('admin.enrollment.index'), 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z'],
        ['label' => 'Reports & Analytics', 'href' => route('admin.reports.index'), 'icon' => 'M3 3v18h18M7 16l4-4 4 4 5-6m-5 0h5v5'],
    ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pb-10 pt-6">

    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
            <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            <span>Some details need fixing. Check the highlighted fields below.</span>
        </div>
    @endif

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

        {{-- ===== Access card + shortcuts ===== --}}
        <aside class="space-y-4 lg:sticky lg:top-24 lg:col-span-4">
            <section class="bg-navy relative overflow-hidden rounded-3xl p-6 text-white shadow-[0_24px_48px_-20px_rgb(22_36_79/0.7)]">
                <div class="absolute inset-0 opacity-[.12] [background-image:radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full border-[28px] border-white/[.05]"></div>

                <div class="relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/Enrollment logo.png') }}" alt="" class="h-9 w-9 rounded-lg bg-white/90 p-0.5">
                            <div class="leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-[1.6px] text-gold">Admin Access</p>
                                <p class="text-xs text-white/60">Learning Management System</p>
                            </div>
                        </div>
                        {{-- smart-card chip --}}
                        <span class="h-8 w-11 rounded-md bg-linear-to-br from-[#ffd979] via-gold to-gold-deep shadow-inner ring-1 ring-black/10 [background-image:linear-gradient(135deg,#ffd979,#f4b301_55%,#c85a08)]">
                            <span class="block h-full w-full rounded-md [background:repeating-linear-gradient(90deg,transparent_0_9px,rgb(0_0_0/.18)_9px_10px),repeating-linear-gradient(0deg,transparent_0_9px,rgb(0_0_0/.18)_9px_10px)]"></span>
                        </span>
                    </div>

                    <div class="mt-6 flex items-center gap-4">
                        <span class="relative flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/10 font-display text-3xl font-bold ring-2 ring-gold/70">
                            {{ $initials }}
                            <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full ring-[3px] ring-[#16244f] {{ $active ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                        </span>
                        <div class="min-w-0">
                            <h1 class="truncate font-display text-xl font-bold tracking-[-.3px]">{{ $fullName }}</h1>
                            <p class="truncate text-sm text-white/65">{{ $user->email }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="rounded-full bg-gold/20 px-2.5 py-0.5 text-[11px] font-bold text-gold">{{ $user->role }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-0.5 text-[11px] font-semibold text-white/85">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>{{ $user->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- System record (read-only) --}}
                    <dl class="mt-6 grid grid-cols-2 gap-x-4 gap-y-3 border-t border-dashed border-white/15 pt-5 text-sm">
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wider text-white/45">User ID</dt>
                            <dd class="mt-0.5 font-mono font-semibold tracking-wide">{{ $userCode }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wider text-white/45">Account</dt>
                            <dd class="mt-0.5 font-semibold {{ $user->is_deleted ? 'text-red-300' : 'text-emerald-300' }}">{{ $user->is_deleted ? 'Deleted' : 'In good standing' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wider text-white/45">Member since</dt>
                            <dd class="mt-0.5 font-semibold">{{ $user->created_at?->format('M d, Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wider text-white/45">Last updated</dt>
                            <dd class="mt-0.5 font-semibold" title="{{ $user->updated_at?->format('M d, Y g:i A') }}">{{ $user->updated_at?->diffForHumans() ?? '—' }}</dd>
                        </div>
                    </dl>

                    {{-- barcode strip --}}
                    <div class="mt-5 flex items-end justify-between gap-4">
                        <div class="h-8 flex-1 opacity-70 [background:repeating-linear-gradient(90deg,#fff_0_2px,transparent_2px_4px,#fff_4px_5px,transparent_5px_8px,#fff_8px_11px,transparent_11px_13px)]"></div>
                        <span class="font-mono text-[10px] tracking-[2px] text-white/50">{{ $user->user_id }}</span>
                    </div>
                </div>
                <div class="absolute inset-x-0 bottom-0 h-[3px] bg-linear-to-r from-transparent via-gold to-transparent opacity-80"></div>
            </section>

            <nav class="hidden rounded-3xl border border-[#e3e8f4] bg-white/70 p-4 lg:block dark:border-[#24386f] dark:bg-white/5" aria-label="Admin modules">
                <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-ink/45 dark:text-slate-500">What you manage</p>
                <ul class="mt-2 space-y-0.5">
                    @foreach ($modules as $module)
                        <li>
                            <a href="{{ $module['href'] }}" class="group flex items-center gap-3 rounded-xl px-2 py-2 text-sm font-semibold text-ink/70 transition hover:bg-white hover:text-ink hover:shadow-sm dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#eef3fd] text-brand transition group-hover:bg-linear-150 group-hover:from-[#3a52a0] group-hover:to-ink group-hover:text-white dark:bg-blue-500/15 dark:text-blue-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $module['icon'] }}"/></svg>
                                </span>
                                {{ $module['label'] }}
                                <svg class="ml-auto h-4 w-4 text-ink/30 transition group-hover:translate-x-0.5 group-hover:text-ink/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        {{-- ===== Settings form ===== --}}
        <form method="POST" action="{{ route('admin.users.update', $user->user_id) }}" class="space-y-5 lg:col-span-8"
              data-confirm="Save account changes?" data-confirm-icon="warning" data-confirm-button="Save changes"
              data-confirm-text="Changing your own role or status can remove your admin access. If you entered a new password, use it the next time you sign in.">
            @csrf
            @method('PUT')

            {{-- Toolbar: section links + actions, stays under the top bar while scrolling --}}
            <div class="sticky top-[4.75rem] z-10 flex flex-col gap-3 rounded-2xl border border-[#e3e8f4] bg-white/85 p-2 shadow-[0_8px_24px_-14px_rgb(22_36_79/0.35)] backdrop-blur sm:flex-row sm:items-center sm:justify-between dark:border-[#24386f] dark:bg-[#0f1a38]/85">
                <div class="hidden items-center gap-0.5 px-1 sm:flex">
                    @foreach ($sections as $i => $section)
                        <a href="#{{ $section['id'] }}" class="whitespace-nowrap rounded-lg px-2.5 py-1.5 text-xs font-semibold text-ink/60 transition hover:bg-[#eef3fd] hover:text-brand-deep dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white">
                            <span class="text-ink/30 dark:text-white/30">0{{ $i + 1 }}</span> {{ $section['label'] }}
                        </a>
                    @endforeach
                </div>
                <div class="flex gap-2 px-1 sm:px-0">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex min-h-10 flex-1 items-center justify-center whitespace-nowrap rounded-xl border border-[#d5dcec] bg-white px-4 text-sm font-semibold text-ink/75 transition hover:bg-[#f0f4fd] hover:text-brand-deep sm:flex-none dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
                    <button type="submit" class="btn-navy flex-1 !min-h-10 sm:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Save changes
                    </button>
                </div>
            </div>

            {{-- 01 Identity --}}
            <section id="identity" class="{{ $panel }}">
                <div class="{{ $panelHead }}">
                    <span class="{{ $step }}">01</span>
                    <div>
                        <h2 class="{{ $panelTitle }}">Identity</h2>
                        <p class="{{ $panelText }}">Your name as it appears across the system, plus birthdate and gender.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-3 sm:p-7">
                    <div>
                        <label for="first_name" class="{{ $label }}">First name <span class="{{ $req }}">*</span></label>
                        <input type="text" id="first_name" name="first_name" maxlength="50" required value="{{ old('first_name', $user->first_name) }}" class="{{ $input }}" placeholder="Juan">
                        @error('first_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="middle_name" class="{{ $label }}">Middle name</label>
                        <input type="text" id="middle_name" name="middle_name" maxlength="50" value="{{ old('middle_name', $user->middle_name) }}" class="{{ $input }}" placeholder="Optional">
                        @error('middle_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="{{ $label }}">Last name <span class="{{ $req }}">*</span></label>
                        <input type="text" id="last_name" name="last_name" maxlength="50" required value="{{ old('last_name', $user->last_name) }}" class="{{ $input }}" placeholder="Dela Cruz">
                        @error('last_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="birthdate" class="{{ $label }}">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}" class="{{ $input }}">
                        @error('birthdate') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
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

            {{-- 02 Contact --}}
            <section id="contact" class="{{ $panel }}">
                <div class="{{ $panelHead }}">
                    <span class="{{ $step }}">02</span>
                    <div>
                        <h2 class="{{ $panelTitle }}">Contact</h2>
                        <p class="{{ $panelText }}">Your sign-in email and how the school can reach you.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 p-6 sm:grid-cols-2 sm:p-7">
                    <div>
                        <label for="email" class="{{ $label }}">Email address <span class="{{ $req }}">*</span></label>
                        <div class="relative">
                            <svg class="{{ $fieldIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                            <input type="email" id="email" name="email" maxlength="100" required value="{{ old('email', $user->email) }}" class="{{ $input }} {{ $withIcon }}" placeholder="name@school.edu">
                        </div>
                        <p class="{{ $hint }}">You sign in with this address.</p>
                        @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contact_number" class="{{ $label }}">Contact number</label>
                        <div class="relative">
                            <svg class="{{ $fieldIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 0 1 2-2h3.28a1 1 0 0 1 .948.684l1.498 4.493a1 1 0 0 1-.502 1.21l-2.257 1.13a11.042 11.042 0 0 0 5.516 5.516l1.13-2.257a1 1 0 0 1 1.21-.502l4.493 1.498a1 1 0 0 1 .684.949V19a2 2 0 0 1-2 2h-1C9.716 21 3 14.284 3 6V5Z"/></svg>
                            <input type="tel" id="contact_number" name="contact_number" maxlength="20" value="{{ old('contact_number', $user->contact_number) }}" class="{{ $input }} {{ $withIcon }}" placeholder="09XX XXX XXXX">
                        </div>
                        @error('contact_number') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="{{ $label }}">Address</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3.5 top-3 h-4 w-4 text-ink/35 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657 13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0ZM15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <textarea id="address" name="address" rows="2" maxlength="255" class="{{ $input }} {{ $withIcon }} resize-none" placeholder="Street, Barangay, City, Province">{{ old('address', $user->address) }}</textarea>
                        </div>
                        <p class="{{ $hint }}">Up to 255 characters.</p>
                        @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- 03 Access --}}
            <section id="access" class="{{ $panel }}">
                <div class="{{ $panelHead }}">
                    <span class="{{ $step }}">03</span>
                    <div>
                        <h2 class="{{ $panelTitle }}">Access</h2>
                        <p class="{{ $panelText }}">Your role and account status.</p>
                    </div>
                </div>
                <div class="space-y-5 p-6 sm:p-7">
                    <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <p><span class="font-bold">You are editing your own access.</span> Changing your role away from Admin, or your status away from Active, can lock you out of the admin panel.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-[14rem_1fr]">
                        <div>
                            <label for="role" class="{{ $label }}">Role <span class="{{ $req }}">*</span></label>
                            <div class="relative">
                                <svg class="{{ $fieldIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z"/></svg>
                                <select id="role" name="role" required class="{{ $input }} {{ $withIcon }}">
                                    @foreach (['Admin', 'Staff', 'Registrar', 'Accounting', 'Teacher', 'Student', 'Guardian'] as $role)
                                        <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('role') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <span class="{{ $label }}">Status <span class="{{ $req }}">*</span></span>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach ($statusStyles as $status => $style)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="status" value="{{ $status }}" required class="peer sr-only" {{ old('status', $user->status) === $status ? 'checked' : '' }}>
                                        <span class="flex items-center justify-center gap-2 rounded-xl border border-[#d5dcec] bg-white px-3 py-2.5 text-sm font-semibold text-ink/60 transition hover:border-brand/40 peer-focus-visible:ring-2 peer-focus-visible:ring-brand/40 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 {{ $style['on'] }}">
                                            <span class="h-2 w-2 rounded-full {{ $style['dot'] }}"></span>{{ $status }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>

            {{-- 04 Security --}}
            <section id="security" class="{{ $panel }}" x-data="{ show: false }">
                <div class="{{ $panelHead }}">
                    <span class="{{ $step }}">04</span>
                    <div>
                        <h2 class="{{ $panelTitle }}">Security</h2>
                        <p class="{{ $panelText }}">Leave blank to keep your current password.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-5 p-6 sm:p-7 md:grid-cols-[1fr_15rem]">
                    <div>
                        <label for="password" class="{{ $label }}">New password</label>
                        <div class="relative">
                            <svg class="{{ $fieldIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 0 1 2 2m4 0a6 6 0 0 1-7.743 5.743L11 17H9v2H7v2H4a1 1 0 0 1-1-1v-2.586a1 1 0 0 1 .293-.707l5.964-5.964A6 6 0 1 1 21 9Z"/></svg>
                            <input :type="show ? 'text' : 'password'" type="password" id="password" name="password" minlength="8" autocomplete="new-password" class="{{ $input }} {{ $withIcon }} pr-11" placeholder="At least 8 characters">
                            <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'"
                                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-ink/45 transition hover:text-brand dark:text-slate-400 dark:hover:text-blue-300">
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 0 1 1.563-3.029m5.858.908a3 3 0 1 1 4.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532 3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0 1 12 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 0 1-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                    <ul class="space-y-2 rounded-2xl bg-[#f5f7fd] p-4 text-xs text-ink/65 ring-1 ring-[#e3e8f4] dark:bg-white/5 dark:text-slate-300 dark:ring-white/10">
                        <li class="flex gap-2"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>At least 8 characters</li>
                        <li class="flex gap-2"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>Mix letters, numbers and symbols</li>
                        <li class="flex gap-2"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-gold"></span>Admin accounts protect every record. Never reuse this password elsewhere.</li>
                    </ul>
                </div>
            </section>
        </form>
    </div>
</div>
@endsection
