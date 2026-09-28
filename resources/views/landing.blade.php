{{-- Student portal landing page, ported from EnrollmentMS (app/Admission/View/index.php
     + public/assets/css/shared/landing.css) to Tailwind. Photos live in public/images/landing.
     Names, quotes and staff below are placeholder copy with stock photos: replace before going public. --}}
@php
    $img = fn ($file) => asset('images/landing/' . $file);
    $year = now()->year;
    $schoolYear = $year . '-' . ($year + 1);

    $strands = [
        ['STEM', 'Science, Technology, Engineering, and Mathematics', '<path d="M9 2v6.5L4.2 17A2 2 0 0 0 6 20h12a2 2 0 0 0 1.8-3L15 8.5V2"/><path d="M8 2h8"/><path d="M7.5 14h9"/>'],
        ['ABM', 'Accountancy, Business, and Management', '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M7 14h4"/><path d="M14 12v5"/><path d="M17 11v6"/>'],
        ['HUMSS', 'Humanities and Social Sciences', '<path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
        ['GAS', 'General Academic Strand', '<circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-2 5.5-5.5 2 2-5.5Z"/>'],
        ['TVL', 'Technical-Vocational-Livelihood', '<path d="M14.7 6.3a4 4 0 0 0 5.3 5.3l-8 8a2.8 2.8 0 0 1-4-4l8-8Z"/>'],
    ];

    $checks = ['No account required', 'Guided five-step form', 'Upload documents online', 'Track status anytime', 'Read registrar remarks', 'Pay tuition online'];

    $steps = [
        ['Fill out the form', 'Enter your personal details, family contacts, and pick your grade level and strand in a guided five-step form.', 'from-[#4b7ae4] to-brand-deep shadow-[0_10px_20px_rgba(47,95,208,.3)]', 'rgba(47,95,208,.07)'],
        ['Upload your documents', 'Attach clear photos or scans of each requirement — JPG or PNG, up to 500 KB per file.', 'from-[#f8bd06] to-gold-deep shadow-[0_10px_20px_rgba(200,90,8,.3)]', 'rgba(200,90,8,.08)'],
        ['Track your application', "Save the reference number you get after submitting, then check back anytime for the registrar's decision and remarks.", 'from-[#34d073] to-[#16a34a] shadow-[0_10px_20px_rgba(22,163,74,.3)]', 'rgba(22,163,74,.08)'],
    ];

    $docIcons = [
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/>',
        'shield' => '<path d="M12 22s8-3.6 8-10V5l-8-3-8 3v7c0 6.4 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        'photo' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/>',
        'transfer' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 15h8"/><path d="m13 12 3 3-3 3"/>',
    ];
    $requirements = [
        ['Form 138 (Report Card)', 'file', false],
        ['Certificate of Good Moral Character', 'shield', false],
        ['PSA Birth Certificate', 'file', false],
        ['2x2 ID Photo', 'photo', false],
        ['Certificate of Transfer / Honorable Dismissal', 'transfer', true],
    ];

    $voices = [
        ['voice-5.jpg', 'Andrea Salcedo', 'Grade 11 · STEM'],
        ['voice-6.jpg', 'Miguel Ramos', 'Grade 12 · TVL'],
        ['voice-7.jpg', 'Kyla Bautista', 'Grade 11 · ABM'],
        ['voice-8.jpg', 'Josh Delacruz', 'Grade 12 · HUMSS'],
    ];
    $quotes = [
        ['voice-9.jpg', 'Trisha Mendoza', 'Incoming Grade 11 · GAS', 'I finished the whole form on my phone during lunch break. Uploading the report card was the only part that took a while, and the portal told me right away that my photo was too big.'],
        ['voice-10.jpg', 'Paolo Villanueva', 'Transferee · Grade 12 · STEM', 'As a transferee I was worried about the extra document. The requirements list showed exactly which one applied to me, so I only had to visit my old school once.'],
    ];

    $stats = [['5', 'Strands to choose from'], ['2', 'Grade levels accepted'], ['10 min', 'Average time to apply'], ['24/7', 'Portal access, any device']];

    $team = [
        ['staff-1.jpg', 'Ma. Elena Reyes', 'School Registrar'],
        ['staff-2.jpg', 'Jasmine Cruz', 'Admissions Officer'],
        ['staff-3.jpg', 'Daniel Ocampo', 'Records Assistant'],
        ['staff-4.jpg', 'Grace Fernandez', 'Cashier'],
    ];

    $posts = [
        ['news-1.jpg', 'Students reading among library shelves', 'Enrollment', 'Enrollment schedule for the incoming school year', 'Applications are accepted online throughout the enrollment period. Slots per strand are confirmed once your documents are verified.'],
        ['news-2.jpg', 'Students collaborating over a laptop', 'Strands', 'Choosing between an academic track and TVL', "Not sure which strand fits you? Read through the strand descriptions before Step 3 of the form — that's where you'll be asked to pick one."],
        ['news-3.jpg', 'Reviewing printed documents on a desk', 'Requirements', 'Getting your documents photo-ready', 'Lay each document flat under good lighting and keep the file under 500 KB. Clear uploads are verified faster and avoid a return with remarks.'],
    ];

    $title = 'm-0 font-display text-[clamp(27px,3.4vw,41px)] font-bold leading-[1.16] tracking-[-0.6px] text-balance text-ink';
    $lede = 'mx-auto mt-3.5 text-[clamp(15px,1.3vw,16.5px)] leading-[1.72] text-ink/70';
    $card = 'rounded-3xl border border-ink/[.085] bg-linear-to-b from-white to-[#fcfdff] shadow-lp-xs';
    $star = '<svg class="size-[15px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 3 6.5 7 .9-5 4.8 1.2 7-6.2-3.4L5.8 21 7 14.2 2 9.4l7-.9Z"/></svg>';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/logo (1).png') }}">
    <title>Student Portal &middot; Enrollment Management System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script>document.documentElement.classList.add("js-anim")</script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/landing.js'])
</head>
<body class="bg-white min-h-screen overflow-x-hidden px-[clamp(16px,3vw,40px)] pt-[clamp(16px,2.5vw,32px)] pb-16 font-jakarta text-ink antialiased selection:bg-gold/30 [&_[id]]:scroll-mt-24">
{{-- Dot grid, same as the LMS login background --}}
<div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(rgba(100,116,139,.18)_1px,transparent_1px)] bg-size-[22px_22px] [mask-image:radial-gradient(ellipse_at_center,#000_25%,transparent_75%)]" aria-hidden="true"></div>
<div data-progress class="fixed inset-x-0 top-0 z-50 h-[3px] origin-left bg-linear-to-r from-gold via-gold-deep to-brand" aria-hidden="true"></div>
<div class="relative z-[1] mx-auto w-full max-w-[1160px]">

    {{-- Topbar --}}
    <header data-hero="bar" class="relative z-20 mb-[clamp(28px,4.5vw,52px)] flex items-center gap-3.5 rounded-[18px] border border-white/90 bg-white/70 px-[clamp(14px,1.8vw,20px)] py-[11px] shadow-lp-xs backdrop-blur-lg">
        <a href="{{ route('landing') }}" class="-m-1 flex min-w-0 items-center gap-3 rounded-[13px] py-1 pr-2 pl-1 transition hover:bg-ink/5" aria-label="Student Portal home">
            <img src="{{ asset('images/logo (1).png') }}" alt="School crest" class="size-[42px] flex-none object-contain drop-shadow-[0_4px_8px_rgba(24,47,110,.2)]">
            <span class="flex min-w-0 flex-col gap-[3px]">
                <strong class="truncate font-display text-[15.5px] leading-[1.1] tracking-[.2px]">Enrollment Management System</strong>
                <span class="self-start rounded-full bg-brand/10 px-[9px] py-0.5 text-[10px] font-extrabold tracking-[.8px] text-brand-deep uppercase ring-1 ring-brand/20 ring-inset">Student Portal</span>
            </span>
        </a>

        <nav class="ml-auto flex items-center">
            <a href="{{ route('login') }}" class="group relative inline-flex h-10 items-center gap-2 overflow-hidden rounded-xl bg-linear-150 from-[#3a52a0] to-ink to-78% px-5 text-sm font-bold whitespace-nowrap text-white shadow-[0_8px_18px_rgba(22,36,79,.3),inset_0_1px_0_rgba(255,255,255,.18)] transition before:pointer-events-none before:absolute before:inset-y-0 before:-left-[80%] before:w-1/2 before:-skew-x-[20deg] before:bg-linear-[105deg,transparent,rgba(255,255,255,.35),transparent] hover:-translate-y-px hover:shadow-[0_12px_24px_rgba(22,36,79,.4)] hover:before:animate-sheen">
                LMS
                <svg class="size-4 transition group-hover:translate-x-[3px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </nav>
    </header>

    {{-- Hero --}}
    <section class="mt-[clamp(18px,3vw,40px)] grid items-center gap-[clamp(38px,5vw,60px)] lg:grid-cols-[1.04fr_.96fr]">
        <div class="min-w-0">
            <span data-hero="eyebrow" class="inline-flex items-center gap-[9px] rounded-full bg-green-500/10 px-4 py-2 text-[13px] font-bold tracking-[.3px] text-green-700 ring-1 ring-green-500/25 ring-inset">
                <i class="size-2 animate-pulse-dot rounded-full bg-green-500" aria-hidden="true"></i>
                Admissions open &middot; S.Y. {{ $schoolYear }}
            </span>

            <h1 data-hero="title" class="mt-5 font-display text-[clamp(36px,4.7vw,58px)] leading-[1.08] font-bold tracking-[-1.4px] text-balance">
                Start Your <em class="hl">Senior High</em> Journey Here
            </h1>

            <p data-hero="lede" class="mt-5 max-w-[505px] text-[clamp(15.5px,1.35vw,17px)] leading-[1.74] text-ink/70">
                Apply for Grade 11 or 12 online — fill out the form, choose your strand, and upload your requirements. No account needed, and you can track everything with a single reference number.
            </p>

            <div data-hero="trust" class="mt-[38px] flex items-center gap-[15px] border-t border-ink/[.085] pt-[30px]">
                <span class="group flex flex-none" aria-hidden="true">
                    @foreach (['voice-1.jpg', 'voice-2.jpg', 'voice-3.jpg', 'voice-4.jpg'] as $face)
                        <img data-person="student" src="{{ $img($face) }}" alt="" class="size-11 rounded-full border-[3px] border-white bg-[#e7edf7] object-cover object-[50%_18%] shadow-[0_4px_12px_rgba(20,40,100,.16)] transition duration-300 not-first:-ml-3.5 group-hover:-translate-y-[3px]">
                    @endforeach
                </span>
                <span class="text-sm leading-[1.4] font-bold">
                    Grade 11 &amp; 12 &middot; All strands
                    <small class="block text-[12.5px] font-semibold text-ink/50">Incoming and transferee students welcome</small>
                </span>
            </div>
        </div>

        <div data-hero="media" class="relative isolate min-w-0">
            <span data-hero="panel" class="absolute inset-[13%_-5%_-7%_17%] -z-10 rounded-[34px] bg-linear-150 from-[#ffe6cc] to-[#ffc98f] shadow-[inset_0_1px_0_rgba(255,255,255,.55)]" aria-hidden="true"></span>
            {{-- Decorations that loop around the photo --}}
            <svg data-hero-deco="ring" class="absolute -top-5 left-[2%] -z-10 size-28 text-gold/70 sm:size-32" viewBox="0 0 100 100" fill="none" aria-hidden="true">
                <circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="2" stroke-dasharray="4 7" stroke-linecap="round"/>
                <circle cx="50" cy="4" r="4" fill="currentColor"/>
            </svg>
            <span data-hero-deco="dots" class="absolute right-[4%] -bottom-9 -z-10 size-28 bg-[radial-gradient(rgba(47,95,208,.35)_1.5px,transparent_1.5px)] bg-size-[12px_12px]" aria-hidden="true"></span>
            <svg data-hero-deco="spark" class="absolute top-[38%] -left-2 z-[3] size-7 text-brand sm:-left-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0c.6 5.6 3.4 8.6 12 12-8.6 3.4-11.4 6.4-12 12-.6-5.6-3.4-8.6-12-12 8.6-3.4 11.4-6.4 12-12Z"/></svg>
            <svg data-hero-deco="spark" class="absolute -top-3 right-[14%] z-[3] size-5 text-gold" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0c.6 5.6 3.4 8.6 12 12-8.6 3.4-11.4 6.4-12 12-.6-5.6-3.4-8.6-12-12 8.6-3.4 11.4-6.4 12-12Z"/></svg>

            {{-- Photo slideshow: two stacked layers crossfade (resources/js/landing.js) --}}
            <div data-hero="photo" class="relative mx-auto aspect-[4/5] w-full max-w-[452px] overflow-hidden rounded-[30px] bg-[#e7edf7] shadow-lp-lg">
                <img data-slide data-pool="hero" src="{{ $img('hero-1.jpg') }}" alt="A senior high school student" class="absolute inset-0 size-full object-cover object-[50%_22%]">
                <img data-slide src="{{ $img('hero-2.jpg') }}" alt="" class="invisible absolute inset-0 size-full object-cover object-[50%_22%]">
            </div>

            @foreach ([
                ['5 strands', 'Academic &amp; TVL tracks', 'top-[8%] -right-[3%] max-sm:hidden', 'text-gold-deep bg-[#ffe8d2]', '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>'],
                ['Fully online', 'No walk-in needed to apply', 'bottom-[6%] left-0 sm:bottom-[13%] sm:-left-[4%]', 'text-green-700 bg-[#d8f5e3]', '<circle cx="12" cy="12" r="9"/><path d="M3.2 9h17.6"/><path d="M3.2 15h17.6"/><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/>'],
            ] as [$k, $s, $pos, $tone, $icon])
                <span data-float class="absolute z-[2] flex items-center gap-[11px] rounded-[15px] bg-white/95 py-2.5 pr-[15px] pl-[11px] shadow-[0_1px_2px_rgba(12,24,58,.08),0_12px_30px_rgba(12,24,58,.2)] ring-1 ring-white/75 backdrop-blur-xl {{ $pos }}">
                    <i class="grid size-8 flex-none place-items-center rounded-[10px] {{ $tone }}" aria-hidden="true">
                        <svg class="size-[17px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                    </i>
                    <span class="grid gap-px">
                        <span class="font-display text-[14.5px] leading-[1.2] font-bold tracking-[-.2px]">{{ $k }}</span>
                        <span class="text-[11.5px] leading-[1.3] font-semibold text-ink/50">{!! $s !!}</span>
                    </span>
                </span>
            @endforeach
        </div>
    </section>

    {{-- Strands --}}
    <section id="strands" class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="head" class="mx-auto mb-[clamp(28px,4vw,46px)] max-w-[660px] text-center">
            <span class="eyebrow">Senior High Tracks</span>
            <h2 class="{{ $title }}">Explore our <em class="hl">Strands</em></h2>
            <p class="{{ $lede }}">Pick the track that matches where you're headed. You choose your strand inside the application form, and the registrar confirms it once your documents are verified.</p>
        </div>

        <div data-anim="stagger" class="grid grid-cols-[repeat(auto-fit,minmax(198px,1fr))] gap-3.5">
            @foreach ($strands as [$name, $desc, $icon])
                @if ($loop->first)
                    <article class="group relative flex flex-col items-center gap-2 overflow-hidden rounded-[22px] bg-navy px-5 pt-8 pb-[30px] text-center text-white shadow-lp transition duration-300 before:pointer-events-none before:absolute before:-top-20 before:-right-[70px] before:size-[190px] before:rounded-full before:bg-radial before:from-[rgba(55,205,255,.34)] before:to-transparent before:to-70% hover:-translate-y-[7px] hover:shadow-lp-lg">
                        <span class="relative mb-2 grid size-14 place-items-center rounded-[18px] bg-white/10 text-[#ffd66b] ring-1 ring-white/15 ring-inset transition duration-300 group-hover:scale-[1.07] group-hover:-rotate-4" aria-hidden="true">
                @else
                    <article class="group flex flex-col items-center gap-2 rounded-[22px] border border-ink/[.085] bg-linear-to-b from-white to-[#fcfdff] px-5 pt-8 pb-[30px] text-center shadow-lp-xs transition duration-300 hover:-translate-y-[7px] hover:border-gold/40 hover:shadow-lp">
                        <span class="mb-2 grid size-14 place-items-center rounded-[18px] bg-linear-160 from-[#fff2e2] to-peach text-gold-deep ring-1 ring-gold-deep/10 ring-inset transition duration-300 group-hover:scale-[1.07] group-hover:-rotate-4" aria-hidden="true">
                @endif
                        <svg class="size-[27px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                    </span>
                    <h3 class="relative m-0 font-display text-xl font-bold tracking-[-.3px] {{ $loop->first ? 'text-white' : 'text-ink' }}">{{ $name }}</h3>
                    <p class="relative m-0 text-[13.4px] leading-[1.65] text-balance {{ $loop->first ? 'text-white/70' : 'text-ink/70' }}">{{ $desc }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- About --}}
    <section id="about" class="mt-[clamp(58px,8vw,112px)] grid items-center gap-[clamp(38px,5vw,66px)] min-[900px]:grid-cols-[.92fr_1.08fr]">
        <div data-anim="collage" class="relative min-w-0 pb-[52px]">
            <img data-pool="about-main" loading="lazy" src="{{ $img('about-main-1.jpg') }}" alt="Students working together" class="block aspect-[4/3] w-full rounded-[28px] bg-[#e7edf7] object-cover shadow-lp">
            <img data-pool="about-detail" loading="lazy" src="{{ $img('about-detail-1.jpg') }}" alt="Students reviewing their work" class="absolute -right-[3%] bottom-0 aspect-square w-[42%] max-w-[192px] rounded-[22px] border-[6px] border-white bg-[#e7edf7] object-cover shadow-lp">
            <span class="absolute bottom-[62px] left-0 grid gap-px rounded-[18px] bg-linear-140 from-[#24386f] to-ink px-[15px] py-3 text-white shadow-[0_16px_34px_rgba(12,24,58,.3)] sm:-left-3.5 sm:px-[19px] sm:py-[15px]">
                <b class="font-display text-[21px] leading-none sm:text-[25px]">10 min</b>
                <span class="text-[11.5px] font-semibold text-white/65">Average time to apply</span>
            </span>
        </div>

        <div data-anim="head" class="min-w-0">
            <span class="eyebrow">About the Portal</span>
            <h2 class="{{ $title }}">One portal for your <em class="hl">whole enrollment</em></h2>
            <p class="mt-3.5 text-[clamp(15px,1.3vw,16.5px)] leading-[1.72] text-ink/70">Everything you submit here — your personal details, your strand preference, and your scanned requirements — goes straight to the registrar's queue for review. No account to create, no forms to print.</p>

            <ul data-anim="stagger" class="mt-[26px] mb-[30px] grid grid-cols-[repeat(auto-fit,minmax(210px,1fr))] gap-x-[22px] gap-y-[13px]">
                @foreach ($checks as $check)
                    <li class="flex items-center gap-[11px] text-[14.2px] font-semibold">
                        <i class="grid size-[23px] flex-none place-items-center rounded-full bg-linear-150 from-[#34d073] to-[#16a34a] text-white" aria-hidden="true">
                            <svg class="size-[13px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </i>
                        {{ $check }}
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how" class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="head" class="mx-auto mb-[clamp(28px,4vw,46px)] max-w-[660px] text-center">
            <span class="eyebrow">The Process</span>
            <h2 class="{{ $title }}">How it works</h2>
            <p class="{{ $lede }}">Three simple steps from application to enrollment.</p>
        </div>

        <div data-anim="stagger" class="grid grid-cols-[repeat(auto-fit,minmax(258px,1fr))] gap-[18px]">
            @foreach ($steps as [$heading, $text, $badge, $glow])
                <article class="relative overflow-hidden px-7 pt-8 pb-[30px] transition duration-300 hover:-translate-y-1.5 hover:shadow-lp {{ $card }}">
                    <span class="pointer-events-none absolute -top-[30px] -right-[26px] size-[122px] rounded-full" style="background: radial-gradient(circle, {{ $glow }}, transparent 70%)" aria-hidden="true"></span>
                    <span class="mb-[18px] grid size-[46px] place-items-center rounded-[15px] bg-linear-150 font-display text-lg font-bold text-white {{ $badge }}" aria-hidden="true">0{{ $loop->iteration }}</span>
                    <h3 class="mb-2 font-display text-[18.5px] font-bold tracking-[-.2px]">{{ $heading }}</h3>
                    <p class="text-[14.2px] leading-[1.68] text-ink/70">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Requirements --}}
    <section id="requirements" class="mt-[clamp(58px,8vw,112px)] grid overflow-hidden rounded-[28px] border border-ink/[.085] bg-white shadow-lp min-[860px]:grid-cols-[.86fr_1.14fr]">
        <div data-anim="head" class="relative flex flex-col justify-center overflow-hidden bg-navy p-[clamp(30px,3.6vw,46px)] text-white before:absolute before:-top-[110px] before:-right-[90px] before:size-[260px] before:rounded-full before:bg-radial before:from-[rgba(55,205,255,.35)] before:to-transparent before:to-68%">
            <h2 class="relative mb-3 font-display text-[clamp(22px,2.4vw,29px)] leading-[1.2] font-bold tracking-[-.4px]">What you'll need</h2>
            <p class="relative mb-[22px] text-[14.4px] leading-[1.72] text-white/70">Prepare these documents before you start. Take clear, well-lit photos where all text is readable — blurry uploads may be returned with remarks.</p>
            <span class="relative self-start rounded-full bg-[#ffd66b]/10 px-[15px] py-2 font-["Roboto_Mono",monospace] text-[11.5px] font-medium tracking-[.3px] text-[#ffd66b] ring-1 ring-[#ffd66b]/25 ring-inset">JPG or PNG &middot; up to 500 KB per file</span>
        </div>

        <ul data-anim="stagger" class="flex flex-col justify-center px-[clamp(16px,2vw,30px)] py-[clamp(12px,1.6vw,22px)]">
            @foreach ($requirements as [$name, $icon, $transfereeOnly])
                <li class="-mx-2 flex items-center gap-[15px] rounded-[14px] px-3 py-4 text-[14.8px] font-semibold transition not-first:border-t not-first:border-ink/[.085] hover:bg-brand/5">
                    <span class="grid size-[42px] flex-none place-items-center rounded-[13px] bg-linear-160 from-[#fff2e2] to-peach text-gold-deep ring-1 ring-gold-deep/10 ring-inset" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $docIcons[$icon] !!}</svg>
                    </span>
                    <span class="min-w-0 flex-1">{{ $name }}</span>
                    @if ($transfereeOnly)
                        <span class="flex-none rounded-full bg-brand/10 px-[11px] py-[5px] text-[10.5px] font-extrabold tracking-[.7px] text-brand-deep uppercase">Transferees only</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Student voices --}}
    <section id="voices" class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="head" class="mx-auto mb-[clamp(28px,4vw,46px)] max-w-[660px] text-center">
            <span class="eyebrow">Student Voices</span>
            <h2 class="{{ $title }}">What students say about the <em class="hl">portal</em></h2>
            <p class="{{ $lede }}">Sample feedback from senior high applicants who enrolled through this portal.</p>
        </div>

        <div data-anim="stagger" class="mb-[15px] grid grid-cols-[repeat(auto-fit,minmax(184px,1fr))] gap-[15px]">
            @foreach ($voices as [$photo, $name, $meta])
                <figure data-person="student" class="group relative overflow-hidden rounded-3xl shadow-lp transition duration-300 hover:-translate-y-[7px] hover:shadow-lp-lg">
                    <img loading="lazy" src="{{ $img($photo) }}" alt="Portrait of a student" class="block aspect-[4/5] w-full bg-[#e7edf7] object-cover object-[50%_22%] transition duration-500 group-hover:scale-[1.045]">
                    <figcaption class="absolute inset-x-0 bottom-0 bg-linear-to-b from-transparent via-[rgba(9,18,44,.74)] via-70% to-[rgba(9,18,44,.92)] px-[18px] pt-[54px] pb-[17px] text-white [text-shadow:0_1px_3px_rgba(0,0,0,.34)]">
                        <b data-name class="block text-[15.5px] leading-[1.25] tracking-[-.2px]">{{ $name }}</b>
                        <span class="text-xs font-semibold text-white/80">{{ $meta }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>

        <div data-anim="stagger" class="grid grid-cols-[repeat(auto-fit,minmax(290px,1fr))] gap-[15px]">
            @foreach ($quotes as [$photo, $name, $meta, $quote])
                <article data-person="student" class="flex gap-[17px] p-7 {{ $card }}">
                    <img loading="lazy" src="{{ $img($photo) }}" alt="Portrait of a student" class="size-[62px] flex-none rounded-full bg-[#e7edf7] object-cover object-[50%_18%] shadow-[0_0_0_3px_#fff,0_0_0_4.5px_rgba(22,36,79,.08)]">
                    <div class="min-w-0">
                        <div class="mb-2.5 flex gap-0.5 text-gold" aria-label="Rated 5 out of 5">{!! str_repeat($star, 5) !!}</div>
                        <p class="mb-3 text-[14.6px] leading-[1.72] text-ink/70">"{{ $quote }}"</p>
                        <b data-name class="block text-sm">{{ $name }}</b>
                        <small class="text-[12.2px] font-semibold text-ink/50">{{ $meta }}</small>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Stats --}}
    <section class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="stagger" class="relative grid grid-cols-[repeat(auto-fit,minmax(148px,1fr))] gap-[clamp(22px,3vw,40px)] overflow-hidden rounded-[28px] bg-navy px-[clamp(26px,3.4vw,46px)] py-[clamp(34px,4.2vw,56px)] shadow-lp-lg before:pointer-events-none before:absolute before:-top-60 before:left-[12%] before:size-[460px] before:rounded-full before:bg-radial before:from-[rgba(55,205,255,.2)] before:to-transparent before:to-68%">
            @foreach ($stats as [$value, $label])
                <div class="relative text-center">
                    <b data-count class="block bg-linear-120 from-[#ffd66b] to-gold bg-clip-text font-display text-[clamp(30px,3.6vw,44px)] leading-none tracking-[-1px] text-transparent tabular-nums">{{ $value }}</b>
                    <span class="mt-[9px] block text-[12.8px] leading-normal font-semibold text-white/65">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Team --}}
    <section id="team" class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="head" class="mx-auto mb-[clamp(28px,4vw,46px)] max-w-[660px] text-center">
            <span class="eyebrow">Who Reviews Your Application</span>
            <h2 class="{{ $title }}">Meet the <em class="hl">Admissions</em> Office</h2>
            <p class="{{ $lede }}">Real people read every submission. If something is missing or unclear, they leave remarks you can see from the status page.</p>
        </div>

        <div data-anim="stagger" class="grid grid-cols-[repeat(auto-fit,minmax(190px,1fr))] gap-[18px]">
            @foreach ($team as [$photo, $name, $role])
                <figure data-person="staff" class="group text-center">
                    <img loading="lazy" src="{{ $img($photo) }}" alt="Portrait of a staff member" class="block aspect-[4/5] w-full rounded-[22px] bg-[#e7edf7] object-cover object-[50%_20%] shadow-lp transition duration-300 group-hover:-translate-y-[7px] group-hover:shadow-lp-lg">
                    <b data-name class="mt-4 block text-[15.5px] tracking-[-.2px]">{{ $name }}</b>
                    <span class="text-[12.8px] font-semibold text-ink/50">{{ $role }}</span>
                </figure>
            @endforeach
        </div>
    </section>

    {{-- Message band --}}
    <section class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="pop" class="rounded-[28px] bg-linear-[118deg,#f8bd06,#f2a005_42%,#e07a06_74%,#c85a08] px-[clamp(28px,4vw,56px)] py-[clamp(34px,4.2vw,54px)] text-center text-white shadow-[0_24px_50px_rgba(200,90,8,.28)]">
            <h2 class="mx-auto mb-2.5 max-w-[720px] font-display text-[clamp(22px,2.7vw,33px)] leading-[1.2] font-bold tracking-[-.6px] text-balance [text-shadow:0_1px_2px_rgba(120,60,0,.18)]">Apply anytime, anywhere, on any device</h2>
            <p class="mx-auto max-w-[580px] text-[15px] leading-[1.66] text-balance text-white/95">Your progress stays on the device you're using, so you can gather a missing document and come back to finish later.</p>
        </div>
    </section>

    {{-- Bulletin --}}
    <section id="bulletin" class="mt-[clamp(58px,8vw,112px)]">
        <div data-anim="head" class="mx-auto mb-[clamp(28px,4vw,46px)] max-w-[660px] text-center">
            <span class="eyebrow">Announcements</span>
            <h2 class="{{ $title }}">The Latest from the <em class="hl">Bulletin</em></h2>
            <p class="{{ $lede }}">Reminders from the registrar's office about enrollment, strands, and requirements.</p>
        </div>

        <div data-anim="stagger" class="grid grid-cols-[repeat(auto-fit,minmax(272px,1fr))] gap-5">
            @foreach ($posts as [$photo, $alt, $tag, $heading, $text])
                <article class="group flex flex-col overflow-hidden rounded-[28px] border border-ink/[.085] bg-white transition duration-300 hover:-translate-y-1.5 hover:shadow-lp">
                    <div class="relative overflow-hidden">
                        <img data-pool="news" loading="lazy" src="{{ $img($photo) }}" alt="{{ $alt }}" class="block aspect-[8/5] w-full bg-[#e7edf7] object-cover transition duration-500 group-hover:scale-105">
                        <span class="absolute top-[15px] left-[15px] rounded-full bg-white/90 px-3.5 py-[7px] text-[10.5px] font-extrabold tracking-[.9px] uppercase shadow-[0_4px_14px_rgba(12,24,58,.2)] backdrop-blur-md">{{ $tag }}</span>
                    </div>
                    <div class="flex flex-col gap-[9px] px-6 pt-6 pb-[26px]">
                        <h3 class="font-display text-[17.5px] leading-[1.32] font-bold tracking-[-.2px]">{{ $heading }}</h3>
                        <p class="text-sm leading-[1.66] text-ink/70">{{ $text }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Footer --}}
    <footer data-anim="stagger" class="mt-[clamp(58px,8vw,112px)] grid grid-cols-[repeat(auto-fit,minmax(196px,1fr))] gap-[clamp(26px,3.4vw,48px)] border-t border-ink/[.085] pt-[clamp(34px,4vw,50px)] text-[13.6px]">
        <div>
            <div class="mb-3.5 flex items-center gap-3">
                <img src="{{ asset('images/logo (1).png') }}" alt="School crest" class="size-[42px] object-contain">
                <strong class="font-display text-[15px] leading-[1.25]">Enrollment Management<br>System</strong>
            </div>
            <p class="leading-[1.68] text-ink/70">The student-facing portal for senior high school admission, enrollment, and payments.</p>
        </div>

        @foreach ([
            'Apply' => [['Get started', route('login')], ['Requirements', '#requirements'], ['How it works', '#how']],
            'Explore' => [['Strands', '#strands'], ['Bulletin', '#bulletin'], ['Pay tuition', route('login')]],
            'Office hours' => ['Monday to Friday, 8:00 AM – 5:00 PM', "Registrar's Office, Administration Building", ['Registrar login', route('registrar.login')], ['Cashier login', route('cashier.login')]],
        ] as $heading => $items)
            <div>
                <h4 class="mb-3.5 text-[11.5px] font-extrabold tracking-[1.2px] text-ink/50 uppercase">{{ $heading }}</h4>
                <ul class="flex flex-col gap-2.5 leading-normal text-ink/70">
                    @foreach ($items as $item)
                        <li>
                            @if (is_array($item))
                                <a href="{{ $item[1] }}" class="font-semibold text-ink hover:text-brand-deep hover:underline">{{ $item[0] }}</a>
                            @else
                                {{ $item }}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </footer>

    <p class="mt-14 text-center text-[14.5px] tracking-[.4px] text-ink/55">&copy; {{ $year }} Enrollment Management System</p>
</div>

<script>
    /* Rotates the stock photos so a different set shows on each visit. Portraits
       keep their name bound to the face; only which slot they land in changes. */
    (function () {
        var BASE = @json(asset('images/landing')) + '/';
        var POOLS = { hero: 5, 'about-main': 4, 'about-detail': 4, news: 6 };
        var PEOPLE = {
            student: [
                ['voice-1.jpg', 'Andrea Salcedo'], ['voice-2.jpg', 'Miguel Ramos'], ['voice-3.jpg', 'Kyla Bautista'],
                ['voice-4.jpg', 'Josh Delacruz'], ['voice-5.jpg', 'Paolo Villanueva'], ['voice-6.jpg', 'Nadine Gutierrez'],
                ['voice-7.jpg', 'Enzo Navarro'], ['voice-8.jpg', 'Trisha Mendoza'], ['voice-9.jpg', 'Rio Alcantara'],
                ['voice-10.jpg', 'Carlo Aquino']
            ],
            staff: [
                ['staff-1.jpg', 'Ma. Elena Reyes'], ['staff-2.jpg', 'Jasmine Cruz'], ['staff-3.jpg', 'Daniel Ocampo'],
                ['staff-4.jpg', 'Grace Fernandez'], ['staff-5.jpg', 'Arnel Bautista'], ['staff-6.jpg', 'Rowena Lim']
            ]
        };

        function shuffle(list) {
            for (var i = list.length - 1; i > 0; i--) {
                var j = Math.floor(Math.random() * (i + 1));
                var tmp = list[i]; list[i] = list[j]; list[j] = tmp;
            }
            return list;
        }

        Object.keys(POOLS).forEach(function (pool) {
            var files = [];
            for (var i = 1; i <= POOLS[pool]; i++) files.push(pool + '-' + i + '.jpg');
            shuffle(files);
            document.querySelectorAll('img[data-pool="' + pool + '"]').forEach(function (img, i) {
                img.src = BASE + files[i % files.length];
            });
        });

        Object.keys(PEOPLE).forEach(function (kind) {
            var picks = shuffle(PEOPLE[kind].slice());
            document.querySelectorAll('[data-person="' + kind + '"]').forEach(function (el, i) {
                var pair = picks[i % picks.length];
                var img = el.tagName === 'IMG' ? el : el.querySelector('img');
                if (img) img.src = BASE + pair[0];
                var label = el.querySelector('[data-name]');
                if (label) label.textContent = pair[1];
            });
        });
    })();
</script>
</body>
</html>
