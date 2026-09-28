<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Flowbite') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Eye blink when toggling password visibility */
        .blink .eye-shape { animation: eye-blink .3s ease; }
        @keyframes eye-blink {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(.1); }
        }

        /* Background decoration */
        .login-bg { background: #fff; }
        .cloud {
            position: absolute; height: auto;
            
            animation: bg-drift 30s ease-in-out infinite alternate;
        }
        .bg-dots {
            background-image: radial-gradient(rgba(100, 116, 139, .18) 1px, transparent 1px);
            background-size: 22px 22px;
            -webkit-mask-image: radial-gradient(ellipse at center, #000 25%, transparent 75%);
            mask-image: radial-gradient(ellipse at center, #000 25%, transparent 75%);
        }
        .bg-glow { background: radial-gradient(ellipse 38% 45% at 50% 50%, rgba(255, 255, 255, .9), rgba(255, 255, 255, .6) 35%, rgba(241, 245, 249, .35) 55%, transparent 70%); }
        @keyframes bg-drift {
            to { transform: translateX(60px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .cloud { animation: none; }
        }

        /* Owl mascot */
        .owl { overflow: visible; -webkit-tap-highlight-color: transparent; }
        .owl-float { animation: owl-float 3.2s ease-in-out infinite; }
        .owl-shadow { transform-origin: 80px 146px; animation: owl-shadow 3.2s ease-in-out infinite; }
        .owl-hop { transform-origin: 80px 140px; }
        .owl-breathe { transform-origin: 80px 140px; animation: owl-breathe 3.2s ease-in-out infinite; }
        .owl-tilt { transform-origin: 80px 136px; transition: transform .45s cubic-bezier(.34, 1.56, .64, 1); }
        .owl-pupil { transition: transform .12s ease-out, opacity .2s; }
        .owl-lid { transform: scaleY(0); transition: transform .35s ease; }
        .owl.blinking .owl-lid { animation: owl-blink .25s ease; }
        .owl.wink .owl-lid-l { animation: owl-blink .45s ease; }
        .owl-brow { transform-box: fill-box; transform-origin: center; transition: transform .3s ease; }
        .owl-cheek { opacity: 0; transition: opacity .3s; }
        .owl-eye-happy, .owl-eye-love, .owl-eye-dizzy { opacity: 0; transition: opacity .2s; }
        .owl-spiral { animation: owl-dizzy 1s linear infinite; }
        .owl-mouth { transform-box: fill-box; transform-origin: top; transform: scaleY(0); }
        .owl.talk .owl-mouth { animation: owl-talk .5s ease; }
        .owl-cap { transition: transform .3s; }
        .owl.capspin .owl-cap { animation: owl-capspin .8s ease; }
        .owl-wing { transition: transform .45s cubic-bezier(.34, 1.56, .64, 1); }
        .owl-wing-l { transform: rotate(12deg); }
        .owl-wing-r { transform: rotate(-12deg); }
        .owl-flap-l { transform-origin: 38px 78px; }
        .owl-flap-r { transform-origin: 122px 78px; }
        .owl.cover .owl-wing-l, .owl.peek .owl-wing-l { transform: translate(24px, -26px) rotate(-8deg) scale(1.5, .85); }
        .owl.cover .owl-wing-r { transform: translate(-24px, -26px) rotate(8deg) scale(1.5, .85); }
        .owl.peek .owl-wing-r { transform: translate(-18px, -6px) rotate(35deg) scale(1.3, .85); }
        .owl.wave .owl-flap-r { animation: owl-wave 1s ease-in-out; }
        .owl.flap .owl-flap-l { animation: owl-flap-l .25s ease-in-out 4; }
        .owl.flap .owl-flap-r { animation: owl-flap-r .25s ease-in-out 4; }
        .owl.fidget .owl-flap { animation: owl-fidget .3s ease; }
        .owl.hop .owl-hop { animation: owl-hop .5s ease; }
        .owl.hop .owl-tassel { animation: owl-sway .6s ease; }
        .owl.shake .owl-hop { animation: owl-shake .5s ease; }
        .owl.jump .owl-hop { animation: owl-jump .8s ease; }
        .owl.giggle .owl-hop { animation: owl-giggle .7s ease; }
        .owl.spin .owl-hop { transform-origin: 80px 90px; animation: owl-spin .9s cubic-bezier(.5, 0, .3, 1.3); }
        .owl.nod .owl-tilt { animation: owl-nod .25s ease; }
        .owl.tilt-l .owl-tilt { transform: rotate(-7deg); }
        .owl.tilt-r .owl-tilt { transform: rotate(7deg); }
        .owl-heart { opacity: 0; transform-box: fill-box; transform-origin: center; }
        .owl.hearts .owl-heart { animation: owl-heart 1.2s ease-out; }
        .owl.hearts .owl-heart:nth-child(2) { animation-delay: .15s; }
        .owl.hearts .owl-heart:nth-child(3) { animation-delay: .3s; }
        .owl-zzz text { opacity: 0; transform-box: fill-box; transform-origin: center; }
        .owl-mark { opacity: 0; transform-box: fill-box; transform-origin: center bottom; }

        /* Moods */
        .owl.shy .owl-cheek, .owl[data-mood="happy"] .owl-cheek, .owl[data-mood="love"] .owl-cheek { opacity: .6; }
        .owl.shy .owl-brow, .owl[data-mood="happy"] .owl-brow, .owl[data-mood="love"] .owl-brow { transform: translateY(-3px); }
        .owl[data-mood="happy"] .owl-pupil, .owl[data-mood="love"] .owl-pupil, .owl[data-mood="dizzy"] .owl-pupil { opacity: 0; }
        .owl[data-mood="happy"] .owl-eye-happy, .owl[data-mood="love"] .owl-eye-love, .owl[data-mood="dizzy"] .owl-eye-dizzy { opacity: 1; }
        .owl[data-mood="surprised"] .owl-brow { transform: translateY(-6px); }
        .owl[data-mood="surprised"] .owl-ex, .owl[data-mood="confused"] .owl-q { opacity: 1; animation: owl-pop .35s cubic-bezier(.34, 1.56, .64, 1); }
        .owl[data-mood="sad"] .owl-brow-l { transform: translateY(-1px) rotate(-16deg); }
        .owl[data-mood="sad"] .owl-brow-r { transform: translateY(-1px) rotate(16deg); }
        .owl[data-mood="confused"] .owl-brow-l { transform: translateY(-6px) rotate(-8deg); }
        .owl[data-mood="confused"] .owl-brow-r { transform: translateY(1px) rotate(-6deg); }
        .owl[data-mood="confused"] .owl-tilt { transform: rotate(9deg); }
        .owl[data-mood="dizzy"] .owl-tilt { animation: owl-wobble 1s ease-in-out infinite; }
        .owl[data-mood="sleepy"] .owl-lid { transform: scaleY(.55); }
        .owl[data-mood="sleepy"] .owl-brow, .owl[data-mood="asleep"] .owl-brow { transform: translateY(3px); }
        .owl[data-mood="asleep"] .owl-lid { transform: scaleY(1); }
        .owl[data-mood="asleep"] .owl-tilt { transform: rotate(-6deg) translateY(2px); }
        .owl[data-mood="asleep"] .owl-breathe { animation-duration: 4.5s; }
        .owl[data-mood="asleep"] .owl-zzz text { animation: owl-z 2.4s ease-in infinite; }
        .owl[data-mood="asleep"] .owl-zzz text:nth-child(2) { animation-delay: .8s; }
        .owl[data-mood="asleep"] .owl-zzz text:nth-child(3) { animation-delay: 1.6s; }

        @keyframes owl-float { 50% { transform: translateY(-4px); } }
        @keyframes owl-shadow { 50% { transform: scale(.85); opacity: .1; } }
        @keyframes owl-breathe { 50% { transform: scale(1.015, 1.025); } }
        @keyframes owl-blink { 50% { transform: scaleY(1); } }
        @keyframes owl-hop { 30% { transform: translateY(-10px); } 60% { transform: translateY(0); } 80% { transform: translateY(-3px); } }
        @keyframes owl-sway { 25% { transform: rotate(-14deg); } 60% { transform: rotate(10deg); } }
        @keyframes owl-shake { 20%, 60% { transform: translateX(-5px); } 40%, 80% { transform: translateX(5px); } }
        @keyframes owl-jump { 15% { transform: scale(1.08, .9); } 45% { transform: translateY(-18px) scale(.95, 1.06); } 75% { transform: scale(1.06, .94); } }
        @keyframes owl-giggle { 15% { transform: rotate(-6deg); } 30% { transform: rotate(6deg); } 45% { transform: rotate(-5deg); } 60% { transform: rotate(5deg); } 75% { transform: rotate(-2deg); } }
        @keyframes owl-spin { to { transform: rotate(360deg); } }
        @keyframes owl-nod { 50% { transform: translateY(2px) rotate(2deg); } }
        @keyframes owl-wobble { 25% { transform: rotate(-5deg); } 75% { transform: rotate(5deg); } }
        @keyframes owl-dizzy { to { transform: rotate(360deg); } }
        @keyframes owl-talk { 25%, 75% { transform: scaleY(1); } 50% { transform: scaleY(.3); } }
        @keyframes owl-capspin { 30% { transform: translateY(-12px) rotate(-15deg); } 60% { transform: translateY(-5px) rotate(10deg); } }
        @keyframes owl-wave { 20%, 60% { transform: rotate(-115deg); } 40%, 80% { transform: rotate(-90deg); } }
        @keyframes owl-flap-l { 50% { transform: rotate(35deg); } }
        @keyframes owl-flap-r { 50% { transform: rotate(-35deg); } }
        @keyframes owl-fidget { 50% { transform: rotate(6deg); } }
        @keyframes owl-heart { 0% { opacity: 0; transform: translateY(6px) scale(.4); } 25% { opacity: 1; transform: scale(1.1); } 100% { opacity: 0; transform: translateY(-22px) scale(.9); } }
        @keyframes owl-z { 0% { opacity: 0; transform: scale(.6); } 20% { opacity: 1; } 100% { opacity: 0; transform: translate(14px, -26px) scale(1.2); } }
        @keyframes owl-pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }

        /* Owl speech bubble */
        .owl-bubble {
            position: absolute; top: 100%; right: .25rem; margin-top: .25rem;
            padding: .4rem .75rem; border-radius: 1rem; border: 1px solid #e0f2fe;
            background: #fff; color: #075985; font-size: .75rem; font-weight: 600; white-space: nowrap;
            box-shadow: 0 10px 24px -10px rgba(2, 132, 199, .5);
            opacity: 0; transform: translateY(-4px) scale(.9); transform-origin: top right; pointer-events: none;
            transition: opacity .2s, transform .25s cubic-bezier(.34, 1.56, .64, 1);
        }
        .owl-bubble::after {
            content: ""; position: absolute; top: -6px; right: 1.75rem; width: 10px; height: 10px;
            background: #fff; border-left: 1px solid #e0f2fe; border-top: 1px solid #e0f2fe; transform: rotate(45deg);
        }
        .owl-bubble.show { opacity: 1; transform: none; }
        @media (min-width: 640px) {
            .owl-bubble { top: 1.75rem; right: 100%; margin: 0 .5rem 0 0; transform: translateX(4px) scale(.9); transform-origin: right center; }
            .owl-bubble::after { top: 50%; right: -6px; margin-top: -5px; border: 0; border-top: 1px solid #e0f2fe; border-right: 1px solid #e0f2fe; }
        }
        @media (prefers-reduced-motion: reduce) {
            .owl * { animation: none !important; transition: none !important; }
            .owl-bubble { transition: none; }
        }
    </style>
</head>
<body class="login-bg min-h-screen antialiased">
    {{-- Decorative background --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        {{-- Realistic clouds: soft shapes roughened by fractal noise, bluish underside peeks below --}}
        <svg width="0" height="0" style="position: absolute">
            <defs>
                <filter id="cloud-f1" x="-30%" y="-50%" width="160%" height="200%">
                    <feTurbulence type="fractalNoise" baseFrequency=".011" numOctaves="5" seed="3"/>
                    <feDisplacementMap in="SourceGraphic" scale="110" xChannelSelector="R" yChannelSelector="G"/>
                    <feGaussianBlur stdDeviation="18"/>
                </filter>
                <filter id="cloud-f2" x="-30%" y="-50%" width="160%" height="200%">
                    <feTurbulence type="fractalNoise" baseFrequency=".011" numOctaves="5" seed="11"/>
                    <feDisplacementMap in="SourceGraphic" scale="110" xChannelSelector="R" yChannelSelector="G"/>
                    <feGaussianBlur stdDeviation="18"/>
                </filter>
                <filter id="cloud-f3" x="-30%" y="-50%" width="160%" height="200%">
                    <feTurbulence type="fractalNoise" baseFrequency=".011" numOctaves="5" seed="27"/>
                    <feDisplacementMap in="SourceGraphic" scale="110" xChannelSelector="R" yChannelSelector="G"/>
                    <feGaussianBlur stdDeviation="18"/>
                </filter>
                <g id="cloud-shape">
                    <ellipse cx="300" cy="180" rx="220" ry="55"/>
                    <ellipse cx="220" cy="140" rx="110" ry="70"/>
                    <ellipse cx="350" cy="120" rx="135" ry="85"/>
                    <ellipse cx="460" cy="160" rx="90" ry="55"/>
                </g>
            </defs>
        </svg>
        <svg class="cloud" viewBox="0 0 600 300" style="top: 4%; left: -6%; width: 30rem"><use href="#cloud-shape" fill="#bfdbfe" opacity=".35" filter="url(#cloud-f1)" transform="translate(0 18)"/><use href="#cloud-shape" fill="#e0f2fe" opacity=".7" filter="url(#cloud-f1)"/></svg>
        <svg class="cloud" viewBox="0 0 600 300" style="top: 36%; right: -8%; width: 34rem; animation-delay: -10s"><use href="#cloud-shape" fill="#bfdbfe" opacity=".35" filter="url(#cloud-f2)" transform="translate(0 18)"/><use href="#cloud-shape" fill="#e0f2fe" opacity=".7" filter="url(#cloud-f2)"/></svg>
        <svg class="cloud" viewBox="0 0 600 300" style="bottom: 2%; left: 16%; width: 26rem; animation-delay: -20s"><use href="#cloud-shape" fill="#bfdbfe" opacity=".35" filter="url(#cloud-f3)" transform="translate(0 18)"/><use href="#cloud-shape" fill="#e0f2fe" opacity=".7" filter="url(#cloud-f3)"/></svg>
        <svg class="cloud" viewBox="0 0 600 300" style="top: 10%; right: 24%; width: 14rem; opacity: .75; animation-delay: -5s"><use href="#cloud-shape" fill="#bfdbfe" opacity=".35" filter="url(#cloud-f2)" transform="translate(0 18)"/><use href="#cloud-shape" fill="#e0f2fe" opacity=".7" filter="url(#cloud-f2)"/></svg>
        <div class="bg-dots absolute inset-0"></div>
        <div class="bg-glow absolute inset-0"></div>
    </div>
    <section class="min-h-screen flex">
        <div class="relative flex flex-col w-full">

            {{-- Brand --}}
            <a href="{{ url('/') }}" class="absolute top-2 left-2 sm:top-3 sm:left-6">
                <img src="{{ asset('images/logo.png') }}" alt="LMS Admin" class="h-14 sm:h-[4.5rem] w-auto">
            </a>

            {{-- Owl mascot: eyes follow the cursor, covers eyes on password --}}
            <div class="absolute top-4 right-4 sm:top-6 sm:right-10 z-10 select-none" aria-hidden="true">
                <div id="owl-bubble" class="owl-bubble"></div>
                <svg id="owl" class="owl block w-20 sm:w-28 lg:w-32 h-auto cursor-pointer" viewBox="0 0 160 150">
                    <defs>
                        <radialGradient id="owl-g-body" cx="40%" cy="30%" r="75%">
                            <stop offset="0" stop-color="#38bdf8"/>
                            <stop offset=".55" stop-color="#0284c7"/>
                            <stop offset="1" stop-color="#075985"/>
                        </radialGradient>
                        <radialGradient id="owl-g-belly" cx="50%" cy="30%" r="75%">
                            <stop offset="0" stop-color="#ffffff"/>
                            <stop offset="1" stop-color="#bae6fd"/>
                        </radialGradient>
                        <radialGradient id="owl-g-eye" cx="45%" cy="40%" r="60%">
                            <stop offset=".7" stop-color="#ffffff"/>
                            <stop offset="1" stop-color="#dbeafe"/>
                        </radialGradient>
                        <linearGradient id="owl-g-wing" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#0284c7"/>
                            <stop offset="1" stop-color="#0c4a6e"/>
                        </linearGradient>
                        <linearGradient id="owl-g-beak" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#fcd34d"/>
                            <stop offset="1" stop-color="#f59e0b"/>
                        </linearGradient>
                    </defs>

                    <ellipse class="owl-shadow" cx="80" cy="146" rx="34" ry="3.5" fill="#0c4a6e" opacity=".18"/>

                    <g class="owl-float">
                        <g class="owl-hop">
                            <g class="owl-breathe">
                                {{-- Feet --}}
                                <g fill="#f59e0b" stroke="#d97706" stroke-width="1">
                                    <ellipse cx="57" cy="139" rx="4.2" ry="3.6"/>
                                    <ellipse cx="64" cy="140.5" rx="4.2" ry="3.6"/>
                                    <ellipse cx="71" cy="139" rx="4.2" ry="3.6"/>
                                    <ellipse cx="89" cy="139" rx="4.2" ry="3.6"/>
                                    <ellipse cx="96" cy="140.5" rx="4.2" ry="3.6"/>
                                    <ellipse cx="103" cy="139" rx="4.2" ry="3.6"/>
                                </g>

                                <g class="owl-tilt">
                                    {{-- Ear tufts + body --}}
                                    <path d="M40 58C30 48 27 36 29 26c9 6 19 14 26 24Z" fill="#075985"/>
                                    <path d="M120 58c10-10 13-22 11-32-9 6-19 14-26 24Z" fill="#075985"/>
                                    <ellipse cx="80" cy="92" rx="52" ry="47" fill="url(#owl-g-body)"/>
                                    <ellipse cx="80" cy="110" rx="32" ry="27" fill="url(#owl-g-belly)"/>
                                    <path d="M66 104q4 4 8 0m4 0q4 4 8 0m4 0q4 4 8 0M72 114q4 4 8 0m4 0q4 4 8 0M76 124q4 4 8 0" stroke="#7dd3fc" stroke-width="2" fill="none" stroke-linecap="round"/>

                                    {{-- Face --}}
                                    <circle cx="58" cy="74" r="25" fill="#7dd3fc" opacity=".3"/>
                                    <circle cx="102" cy="74" r="25" fill="#7dd3fc" opacity=".3"/>
                                    <circle cx="58" cy="74" r="20" fill="url(#owl-g-eye)" stroke="#0c4a6e" stroke-opacity=".25" stroke-width="2"/>
                                    <circle cx="102" cy="74" r="20" fill="url(#owl-g-eye)" stroke="#0c4a6e" stroke-opacity=".25" stroke-width="2"/>
                                    <g class="owl-pupil" data-cx="58" data-cy="74">
                                        <circle cx="58" cy="74" r="9.5" fill="#0f172a"/>
                                        <circle cx="61.5" cy="70" r="3.2" fill="#fff"/>
                                        <circle cx="55" cy="78" r="1.4" fill="#fff" opacity=".8"/>
                                    </g>
                                    <g class="owl-pupil" data-cx="102" data-cy="74">
                                        <circle cx="102" cy="74" r="9.5" fill="#0f172a"/>
                                        <circle cx="105.5" cy="70" r="3.2" fill="#fff"/>
                                        <circle cx="99" cy="78" r="1.4" fill="#fff" opacity=".8"/>
                                    </g>
                                    <g class="owl-eye-happy" fill="none" stroke="#0f172a" stroke-width="4.5" stroke-linecap="round">
                                        <path d="M47 78q11-13 22 0"/>
                                        <path d="M91 78q11-13 22 0"/>
                                    </g>
                                    <g class="owl-eye-love" fill="#f43f5e">
                                        <path d="M58 83 48 73a5 5 0 0 1 10-5 5 5 0 0 1 10 5Z"/>
                                        <path d="M102 83 92 73a5 5 0 0 1 10-5 5 5 0 0 1 10 5Z"/>
                                    </g>
                                    <g class="owl-eye-dizzy" fill="none" stroke="#0f172a" stroke-width="2.2" stroke-linecap="round">
                                        <path class="owl-spiral" style="transform-origin: 59.5px 74px" d="M58 74a1.5 1.5 0 0 1 3 0 3 3 0 0 1-6 0 4.5 4.5 0 0 1 9 0 6 6 0 0 1-12 0 7.5 7.5 0 0 1 15 0"/>
                                        <path class="owl-spiral" style="transform-origin: 103.5px 74px" d="M102 74a1.5 1.5 0 0 1 3 0 3 3 0 0 1-6 0 4.5 4.5 0 0 1 9 0 6 6 0 0 1-12 0 7.5 7.5 0 0 1 15 0"/>
                                    </g>
                                    <circle class="owl-lid owl-lid-l" cx="58" cy="74" r="21" fill="#0284c7" style="transform-origin: 58px 53px"/>
                                    <circle class="owl-lid owl-lid-r" cx="102" cy="74" r="21" fill="#0284c7" style="transform-origin: 102px 53px"/>
                                    <path class="owl-brow owl-brow-l" d="M44 50q13-7 26-1" stroke="#0c4a6e" stroke-width="4" fill="none" stroke-linecap="round"/>
                                    <path class="owl-brow owl-brow-r" d="M90 49q13-6 26 1" stroke="#0c4a6e" stroke-width="4" fill="none" stroke-linecap="round"/>
                                    <ellipse class="owl-cheek" cx="49" cy="99" rx="5.5" ry="3.2" fill="#fb7185"/>
                                    <ellipse class="owl-cheek" cx="111" cy="99" rx="5.5" ry="3.2" fill="#fb7185"/>
                                    <ellipse class="owl-mouth" cx="80" cy="99" rx="4.5" ry="5" fill="#9a3412"/>
                                    <path d="M72 88q8-4 16 0l-6.5 11a1.8 1.8 0 0 1-3 0Z" fill="url(#owl-g-beak)" stroke="#d97706" stroke-width="1" stroke-linejoin="round"/>

                                    {{-- Graduation cap --}}
                                    <g class="owl-cap" style="transform-origin: 80px 34px">
                                        <path d="M62 30h36v10q-18 6-36 0Z" fill="#1e293b"/>
                                        <polygon points="80,13 126,25 80,37 34,25" fill="#0f172a"/>
                                        <polygon points="80,15 116,24.5 80,22 44,24.5" fill="#fff" opacity=".08"/>
                                        <g class="owl-tassel" style="transform-origin: 80px 25px">
                                            <path d="M80 25l34 4v15" stroke="#fbbf24" stroke-width="2" fill="none" stroke-linecap="round"/>
                                            <path d="M111.5 43h5l1.5 8h-8Z" fill="#fbbf24"/>
                                        </g>
                                        <circle cx="80" cy="25" r="2.6" fill="#fbbf24"/>
                                    </g>

                                    {{-- Wings --}}
                                    <g class="owl-wing owl-wing-l" style="transform-origin: 34px 100px">
                                        <g class="owl-flap owl-flap-l">
                                            <ellipse cx="34" cy="100" rx="14" ry="26" fill="url(#owl-g-wing)"/>
                                            <path d="M28 96q6 4 12 0M28 106q6 4 12 0M30 116q5 3 9 0" stroke="#38bdf8" stroke-opacity=".45" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                                        </g>
                                    </g>
                                    <g class="owl-wing owl-wing-r" style="transform-origin: 126px 100px">
                                        <g class="owl-flap owl-flap-r">
                                            <ellipse cx="126" cy="100" rx="14" ry="26" fill="url(#owl-g-wing)"/>
                                            <path d="M120 96q6 4 12 0M120 106q6 4 12 0M121 116q5 3 9 0" stroke="#38bdf8" stroke-opacity=".45" stroke-width="1.8" fill="none" stroke-linecap="round"/>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </g>

                        {{-- Reaction effects --}}
                        <g class="owl-hearts" fill="#f43f5e">
                            <path class="owl-heart" d="M26 54l-7-7a3.5 3.5 0 0 1 7-4 3.5 3.5 0 0 1 7 4Z"/>
                            <path class="owl-heart" d="M136 50l-7-7a3.5 3.5 0 0 1 7-4 3.5 3.5 0 0 1 7 4Z"/>
                            <path class="owl-heart" d="M108 10l-5-5a2.5 2.5 0 0 1 5-3 2.5 2.5 0 0 1 5 3Z"/>
                        </g>
                        <g class="owl-zzz" fill="#0369a1" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="800">
                            <text x="122" y="48" font-size="12">z</text>
                            <text x="122" y="48" font-size="15">Z</text>
                            <text x="122" y="48" font-size="18">Z</text>
                        </g>
                        <text class="owl-mark owl-q" x="130" y="40" fill="#0369a1" font-family="ui-sans-serif, system-ui, sans-serif" font-size="24" font-weight="800">?</text>
                        <text class="owl-mark owl-ex" x="132" y="40" fill="#f59e0b" font-family="ui-sans-serif, system-ui, sans-serif" font-size="24" font-weight="800">!</text>
                    </g>
                </svg>
            </div>

            <div class="flex flex-1 items-center justify-center px-6 py-24">
                <div class="w-full max-w-sm">
                    <h1 class="text-center text-3xl font-extrabold tracking-tight text-slate-900">
                        Login to Your <span class="bg-gradient-to-r from-sky-500 to-blue-600 bg-clip-text text-transparent">Account</span>
                    </h1>

                    {{-- Session Status --}}
                    @if (session('status'))
                        <div class="relative mt-6 overflow-hidden rounded-xl border border-white/80 bg-white/70 py-2.5 pl-3.5 pr-3 text-xs text-emerald-800 shadow-md shadow-emerald-500/10 ring-1 ring-emerald-200/60 backdrop-blur-md" role="alert">
                            <span class="absolute inset-y-0 left-0 w-0.5 bg-gradient-to-b from-emerald-400 to-teal-500" aria-hidden="true"></span>
                            <div class="flex items-start gap-2.5">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-sm shadow-emerald-500/30">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m5 12.5 4.5 4.5L19 7.5"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 pt-px">
                                    <p class="font-semibold text-gray-900">All set</p>
                                    <p class="leading-snug text-emerald-700/90">{{ session('status') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="relative mt-6 overflow-hidden rounded-xl border border-white/80 bg-white/70 py-2.5 pl-3.5 pr-3 text-xs text-red-800 shadow-md shadow-rose-500/10 ring-1 ring-rose-200/70 backdrop-blur-md" role="alert">
                            <span class="absolute inset-y-0 left-0 w-0.5 bg-gradient-to-b from-rose-400 to-red-500" aria-hidden="true"></span>
                            <div class="flex items-start gap-2.5">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-rose-400 to-red-500 text-white shadow-sm shadow-rose-500/30">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 8v5"/>
                                        <circle cx="12" cy="16.5" r=".6" fill="currentColor"/>
                                        <path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                                    </svg>
                                </span>
                                <div class="min-w-0 pt-px">
                                    <p class="font-semibold text-gray-900">Couldn't sign you in</p>
                                    @if ($errors->count() === 1)
                                        <p class="leading-snug text-red-700/90">{{ $errors->first() }}</p>
                                    @else
                                        <ul class="mt-0.5 space-y-0.5">
                                            @foreach ($errors->all() as $error)
                                                <li class="flex items-start gap-1.5 leading-snug text-red-700/90">
                                                    <span class="mt-[6px] h-1 w-1 shrink-0 rounded-full bg-rose-400" aria-hidden="true"></span>
                                                    <span>{{ $error }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <form class="mt-8" method="POST" action="{{ route('login.authenticate') }}">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="email" class="sr-only">Email</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sky-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                                            <path d="m3 7 9 6 9-6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           value="{{ old('email') ?? old('identifier') }}"
                                           class="block w-full rounded-xl border border-sky-200 bg-white py-3 pl-11 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-200/60"
                                           placeholder="Email"
                                           required
                                           autofocus
                                           autocomplete="username">
                                </div>
                            </div>

                            <div>
                                <label for="password" class="sr-only">Password</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sky-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                                            <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <input type="password"
                                           name="password"
                                           id="password"
                                           placeholder="Password"
                                           class="block w-full rounded-xl border border-sky-200 bg-white py-3 pl-11 pr-12 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-200/60"
                                           required
                                           autocomplete="current-password">
                                    <button type="button"
                                            id="toggle-password"
                                            class="group absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-sky-600 focus:outline-none focus-visible:text-sky-600"
                                            aria-label="Show password"
                                            aria-pressed="false">
                                        <svg class="w-5 h-5 transition-transform duration-200 group-active:scale-90" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                            <g class="eye-shape origin-center [transform-box:fill-box]">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                                <circle class="origin-center [transform-box:fill-box] scale-60 opacity-60 transition duration-300 group-aria-pressed:scale-100 group-aria-pressed:opacity-100" cx="12" cy="12" r="3"/>
                                            </g>
                                            <path class="[stroke-dasharray:26] [stroke-dashoffset:0] transition-[stroke-dashoffset] duration-300 group-aria-pressed:[stroke-dashoffset:26]" d="M3 3l18 18"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center">
                            <input id="remember"
                                   name="remember"
                                   aria-describedby="remember"
                                   type="checkbox"
                                   class="w-4 h-4 rounded border-gray-300 text-sky-600 focus:ring-sky-300"
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember" class="ml-2 text-sm font-medium text-slate-700 cursor-pointer select-none">Remember me</label>
                        </div>

                        <div class="mt-10 flex justify-center">
                            <button type="submit" class="w-40 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:from-sky-600 hover:to-blue-700 hover:shadow-sky-500/40 focus:outline-none focus:ring-4 focus:ring-sky-300 active:scale-[.98]">
                                Login
                            </button>
                        </div>

                        <p class="mt-8 text-center">
                            <a href="{{ route('otp') }}" class="text-sm font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-900">Forgot password?</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            var toggle = document.getElementById('toggle-password');
            var input = document.getElementById('password');
            if (!toggle || !input) return;

            toggle.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

                toggle.classList.remove('blink');
                void toggle.offsetWidth; // restart animation
                toggle.classList.add('blink');
            });
        })();
    </script>

    <script>
        // Owl mascot animation (visual only)
        (function () {
            var owl = document.getElementById('owl');
            var email = document.getElementById('email');
            var pw = document.getElementById('password');
            var toggle = document.getElementById('toggle-password');
            if (!owl || !email || !pw) return;

            var bubble = document.getElementById('owl-bubble');
            var pupils = owl.querySelectorAll('.owl-pupil');
            var locked = false;
            var timers = {};
            var mood = '';
            var lastActive = Date.now();
            var pokes = [];
            var wasOnPw = false;
            var emailWasValid = false;
            var capsOn = false;

            function pick(list) { return list[Math.floor(Math.random() * list.length)]; }

            function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v); }

            function setMood(name, ms) {
                clearTimeout(timers.mood);
                mood = name || '';
                if (mood) owl.setAttribute('data-mood', mood);
                else owl.removeAttribute('data-mood');
                if (ms) timers.mood = setTimeout(function () { setMood(''); }, ms);
            }

            function say(text, ms) {
                if (!bubble) return;
                bubble.textContent = text;
                bubble.classList.add('show');
                play('talk', 500);
                clearTimeout(timers.say);
                timers.say = setTimeout(function () { bubble.classList.remove('show'); }, ms || 2200);
            }

            // Any user activity resets the sleep timer and wakes the owl up
            function wake() {
                lastActive = Date.now();
                if (mood === 'sleepy') setMood('');
                else if (mood === 'asleep') {
                    setMood('surprised', 900);
                    play('hop', 600);
                    say(pick(["Huh? I'm awake!", "Hoo's there?"]), 1800);
                }
            }

            function lookAt(x, y) {
                var r = owl.getBoundingClientRect();
                var s = r.width / 160;
                pupils.forEach(function (p) {
                    var dx = x - (r.left + p.dataset.cx * s);
                    var dy = y - (r.top + p.dataset.cy * s);
                    var d = Math.hypot(dx, dy) || 1;
                    var m = Math.min(7, d / 20);
                    p.style.transform = 'translate(' + (dx / d * m) + 'px,' + (dy / d * m) + 'px)';
                });
            }

            function play(cls, ms) {
                owl.classList.remove(cls);
                owl.getBoundingClientRect(); // restart animation
                owl.classList.add(cls);
                clearTimeout(timers[cls]);
                timers[cls] = setTimeout(function () { owl.classList.remove(cls); }, ms);
            }

            function lookAtInput(input, followText) {
                var r = input.getBoundingClientRect();
                var x = followText ? r.left + 44 + Math.min(input.value.length * 7, r.width - 60) : r.left + r.width / 2;
                lookAt(x, r.top + r.height / 2);
            }

            function update() {
                var el = document.activeElement;
                var onPw = el === pw || el === toggle;
                var shown = pw.type === 'text';
                owl.classList.toggle('cover', onPw && !shown);
                owl.classList.toggle('peek', onPw && shown);
                locked = el === email || onPw;
                if (el === email) lookAtInput(email, true);
                else if (onPw) lookAtInput(pw, false);
                if (onPw && !wasOnPw && !shown) say(pick(["I won't peek, promise!", 'Eyes closed!']), 1600);
                wasOnPw = onPw;
            }

            // Cursor tracking + general activity
            document.addEventListener('pointermove', function (e) {
                wake();
                if (!locked && mood !== 'dizzy') lookAt(e.clientX, e.clientY);
            });
            document.addEventListener('pointerdown', function (e) {
                if (owl.contains(e.target)) return;
                wake();
                lookAt(e.clientX, e.clientY);
                play('blinking', 250);
                play('hop', 600);
            });
            document.addEventListener('keydown', wake);
            document.addEventListener('focusin', update);
            document.addEventListener('focusout', function () { setTimeout(update, 0); });

            // Email: follow the text, nod along, cheer when it looks valid
            email.addEventListener('input', function () {
                lookAtInput(email, true);
                play('nod', 250);
                var valid = isEmail(email.value.trim());
                if (valid && !emailWasValid) {
                    setMood('happy', 1400);
                    say(pick(['Looks good!', 'Nice email!']), 1500);
                }
                emailWasValid = valid;
            });
            email.addEventListener('blur', function () {
                var v = email.value.trim();
                if (v && !isEmail(v)) {
                    setMood('confused', 2000);
                    say('Hmm, is that a real email?', 2200);
                }
            });

            // Password: fidget behind the wings, warn about Caps Lock
            pw.addEventListener('input', function () { play('fidget', 300); });
            function checkCaps(e) {
                if (!e.getModifierState) return;
                var on = e.getModifierState('CapsLock');
                if (on && !capsOn) {
                    setMood('surprised', 1200);
                    play('hop', 600);
                    say('Psst… Caps Lock is on!', 2400);
                }
                capsOn = on;
            }
            pw.addEventListener('keydown', checkCaps);
            pw.addEventListener('keyup', checkCaps);
            if (toggle) toggle.addEventListener('click', function () {
                setTimeout(function () {
                    update();
                    say(pw.type === 'text' ? 'Just a tiny peek… 👀' : 'Okay, eyes closed!', 1600);
                }, 0);
            });

            // Touching the owl: reaction depends on where it gets poked
            owl.addEventListener('pointerenter', function () { owl.classList.add('shy'); });
            owl.addEventListener('pointerleave', function () { owl.classList.remove('shy'); });
            owl.addEventListener('click', function (e) {
                wake();
                var now = Date.now();
                pokes = pokes.filter(function (t) { return now - t < 1500; });
                pokes.push(now);
                if (pokes.length >= 5) {
                    pokes = [];
                    setMood('dizzy', 2600);
                    play('spin', 900);
                    say('Whoa… too many pokes!', 2400);
                    return;
                }
                if (mood === 'dizzy') return;

                var r = owl.getBoundingClientRect();
                var y = (e.clientY - r.top) / (r.width / 160);
                lookAt(e.clientX, e.clientY);
                if (y < 44) {
                    setMood('surprised', 900);
                    play('capspin', 800);
                    say(pick(['Hey, my cap!', 'Careful with the cap!']), 1600);
                } else if (y > 100) {
                    setMood('happy', 1400);
                    play('giggle', 700);
                    say(pick(['Hehe, that tickles!', 'Hoo-hoo-hoo!']), 1600);
                } else if (Math.random() < .5) {
                    setMood('love', 1500);
                    play('hearts', 1400);
                    play('hop', 600);
                    say(pick(['Hoot! Hi there!', "You're my favorite!"]), 1600);
                } else {
                    play('wink', 500);
                    play('wave', 1000);
                    say(pick(['Hoo! Ready to learn?', 'Study hard! 📚']), 1800);
                }
            });

            // Submitting: excited flap
            if (pw.form) pw.form.addEventListener('submit', function () {
                owl.classList.remove('cover', 'peek');
                setMood('happy');
                play('jump', 800);
                play('flap', 1000);
                say(pick(['Good luck!', 'Here we go!']), 3000);
            });

            // Idle: blinking
            (function idleBlink() {
                setTimeout(function () {
                    if (mood !== 'asleep' && mood !== 'dizzy') {
                        play('blinking', 250);
                        if (Math.random() < .2) setTimeout(function () { play('blinking', 250); }, 320);
                    }
                    idleBlink();
                }, 2500 + Math.random() * 3000);
            })();

            // Idle: little fidgets when nobody is interacting
            (function idleFidget() {
                setTimeout(function () {
                    if (!locked && !mood && Date.now() - lastActive > 3500) {
                        var act = pick(['look', 'look', 'tilt', 'hop', 'wave']);
                        if (act === 'look') {
                            var r = owl.getBoundingClientRect();
                            lookAt(r.left + (Math.random() - .5) * 600, r.top + Math.random() * 400);
                        } else if (act === 'tilt') play(Math.random() < .5 ? 'tilt-l' : 'tilt-r', 1400);
                        else play(act, act === 'wave' ? 1000 : 600);
                    }
                    idleFidget();
                }, 3500 + Math.random() * 3500);
            })();

            // Idle: gets sleepy, then dozes off
            setInterval(function () {
                if (locked || document.hidden) return;
                var quiet = Date.now() - lastActive;
                if (!mood && quiet > 20000) setMood('sleepy');
                else if (mood === 'sleepy' && quiet > 27000) setMood('asleep');
            }, 1000);

            // Page state on load
            if (document.querySelector('[role="alert"].text-red-800')) {
                play('shake', 600);
                setMood('sad', 3000);
                say("Oops! Let's try that again.", 3200);
            } else if (document.querySelector('[role="alert"].text-emerald-800')) {
                setMood('happy', 2000);
                play('hop', 600);
                say('Yay! All set.', 2600);
            } else {
                setTimeout(function () { play('wave', 1000); say('Hoo! Welcome back!', 2200); }, 600);
            }
            update();
        })();
    </script>
</body>
</html>
