<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Flowbite') }} - @yield('title')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Background: plain white with the same dot grid as the login page */
        .otp-bg { background: #fff; }
        .bg-dots {
            background-image: radial-gradient(rgba(100, 116, 139, .18) 1px, transparent 1px);
            background-size: 22px 22px;
            -webkit-mask-image: radial-gradient(ellipse at center, #000 25%, transparent 75%);
            mask-image: radial-gradient(ellipse at center, #000 25%, transparent 75%);
        }

        /* Owl mascot (subset of the login owl) */
        .owl { overflow: visible; -webkit-tap-highlight-color: transparent; }
        .owl-float { animation: owl-float 3.2s ease-in-out infinite; }
        .owl-shadow { transform-origin: 80px 146px; animation: owl-shadow 3.2s ease-in-out infinite; }
        .owl-hop { transform-origin: 80px 140px; }
        .owl-breathe { transform-origin: 80px 140px; animation: owl-breathe 3.2s ease-in-out infinite; }
        .owl-tilt { transform-origin: 80px 136px; transition: transform .45s cubic-bezier(.34, 1.56, .64, 1); }
        .owl-pupil { transition: transform .12s ease-out, opacity .2s; }
        .owl-lid { transform: scaleY(0); transition: transform .35s ease; }
        .owl.blinking .owl-lid { animation: owl-blink .25s ease; }
        .owl-brow { transform-box: fill-box; transform-origin: center; transition: transform .3s ease; }
        .owl-cheek { opacity: 0; transition: opacity .3s; }
        .owl-eye-happy { opacity: 0; transition: opacity .2s; }
        .owl-mouth { transform-box: fill-box; transform-origin: top; transform: scaleY(0); }
        .owl.talk .owl-mouth { animation: owl-talk .5s ease; }
        .owl-wing { transition: transform .45s cubic-bezier(.34, 1.56, .64, 1); }
        .owl-wing-l { transform: rotate(12deg); }
        .owl-wing-r { transform: rotate(-12deg); }
        .owl-flap-l { transform-origin: 38px 78px; }
        .owl-flap-r { transform-origin: 122px 78px; }
        .owl.wave .owl-flap-r { animation: owl-wave 1s ease-in-out; }
        .owl.flap .owl-flap-l { animation: owl-flap-l .25s ease-in-out 4; }
        .owl.flap .owl-flap-r { animation: owl-flap-r .25s ease-in-out 4; }
        .owl.hop .owl-hop { animation: owl-hop .5s ease; }
        .owl.shake .owl-hop { animation: owl-shake .5s ease; }
        .owl.jump .owl-hop { animation: owl-jump .8s ease; }
        .owl.nod .owl-tilt { animation: owl-nod .25s ease; }
        .owl.cover .owl-wing-l, .owl.peek .owl-wing-l { transform: translate(24px, -26px) rotate(-8deg) scale(1.5, .85); }
        .owl.cover .owl-wing-r { transform: translate(-24px, -26px) rotate(8deg) scale(1.5, .85); }
        .owl.peek .owl-wing-r { transform: translate(-18px, -6px) rotate(35deg) scale(1.3, .85); }
        /* OTP-only: owl leans toward the digit being typed, holds a "thinking" chin pose while waiting */
        .owl.lean-l .owl-tilt { transform: rotate(-8deg); }
        .owl.lean-r .owl-tilt { transform: rotate(8deg); }
        .owl.think .owl-wing-r { transform: translate(-30px, -14px) rotate(-60deg) scale(.9); }
        .owl-mark { opacity: 0; transform-box: fill-box; transform-origin: center bottom; }

        .owl[data-mood="happy"] .owl-cheek { opacity: .6; }
        .owl[data-mood="happy"] .owl-brow { transform: translateY(-3px); }
        .owl[data-mood="happy"] .owl-pupil { opacity: 0; }
        .owl[data-mood="happy"] .owl-eye-happy { opacity: 1; }
        .owl[data-mood="surprised"] .owl-brow { transform: translateY(-6px); }
        .owl[data-mood="surprised"] .owl-ex, .owl[data-mood="confused"] .owl-q { opacity: 1; animation: owl-pop .35s cubic-bezier(.34, 1.56, .64, 1); }
        .owl[data-mood="sad"] .owl-brow-l { transform: translateY(-1px) rotate(-16deg); }
        .owl[data-mood="sad"] .owl-brow-r { transform: translateY(-1px) rotate(16deg); }
        .owl[data-mood="confused"] .owl-brow-l { transform: translateY(-6px) rotate(-8deg); }
        .owl[data-mood="confused"] .owl-brow-r { transform: translateY(1px) rotate(-6deg); }

        @keyframes owl-float { 50% { transform: translateY(-4px); } }
        @keyframes owl-shadow { 50% { transform: scale(.85); opacity: .1; } }
        @keyframes owl-breathe { 50% { transform: scale(1.015, 1.025); } }
        @keyframes owl-blink { 50% { transform: scaleY(1); } }
        @keyframes owl-hop { 30% { transform: translateY(-10px); } 60% { transform: translateY(0); } 80% { transform: translateY(-3px); } }
        @keyframes owl-shake { 20%, 60% { transform: translateX(-5px); } 40%, 80% { transform: translateX(5px); } }
        @keyframes owl-jump { 15% { transform: scale(1.08, .9); } 45% { transform: translateY(-18px) scale(.95, 1.06); } 75% { transform: scale(1.06, .94); } }
        @keyframes owl-nod { 50% { transform: translateY(2px) rotate(2deg); } }
        @keyframes owl-talk { 25%, 75% { transform: scaleY(1); } 50% { transform: scaleY(.3); } }
        @keyframes owl-wave { 20%, 60% { transform: rotate(-115deg); } 40%, 80% { transform: rotate(-90deg); } }
        @keyframes owl-flap-l { 50% { transform: rotate(35deg); } }
        @keyframes owl-flap-r { 50% { transform: rotate(-35deg); } }
        @keyframes owl-pop { 0% { transform: scale(0); } 100% { transform: scale(1); } }

        .owl-bubble {
            position: absolute; top: 100%; right: .25rem; margin-top: .25rem;
            padding: .4rem .75rem; border-radius: 1rem; border: 1px solid #e0f2fe;
            background: #fff; color: #075985; font-size: .75rem; font-weight: 600; white-space: nowrap;
            box-shadow: 0 10px 24px -10px rgba(2, 132, 199, .5);
            opacity: 0; transform: translateY(-4px) scale(.9); transform-origin: top right; pointer-events: none;
            transition: opacity .2s, transform .25s cubic-bezier(.34, 1.56, .64, 1);
        }
        .owl-bubble.show { opacity: 1; transform: none; }
        @media (min-width: 640px) {
            .owl-bubble { top: 1.75rem; right: 100%; margin: 0 .5rem 0 0; transform: translateX(4px) scale(.9); transform-origin: right center; }
        }
        @media (prefers-reduced-motion: reduce) {
            .owl * { animation: none !important; transition: none !important; }
            .owl-bubble { transition: none; }
        }
    </style>
</head>
<body class="otp-bg min-h-screen antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10" aria-hidden="true">
        <div class="bg-dots absolute inset-0"></div>
    </div>

    <section class="min-h-screen flex">
        <div class="relative flex flex-col w-full">

            <a href="{{ url('/') }}" class="absolute top-2 left-2 sm:top-3 sm:left-6">
                <img src="{{ asset('images/Icon.png') }}" alt="LMS" class="h-12 sm:h-16 w-auto">
            </a>


            {{-- Owl mascot: each page adds its own behaviour on top of window.Owl --}}
            <div class="absolute top-4 right-4 sm:top-6 sm:right-10 z-10 select-none" aria-hidden="true">
                <div id="owl-bubble" class="owl-bubble"></div>
                <svg id="owl" class="owl block w-20 sm:w-28 lg:w-32 h-auto" viewBox="0 0 160 150">
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
                                <g fill="#f59e0b" stroke="#d97706" stroke-width="1">
                                    <ellipse cx="57" cy="139" rx="4.2" ry="3.6"/>
                                    <ellipse cx="64" cy="140.5" rx="4.2" ry="3.6"/>
                                    <ellipse cx="71" cy="139" rx="4.2" ry="3.6"/>
                                    <ellipse cx="89" cy="139" rx="4.2" ry="3.6"/>
                                    <ellipse cx="96" cy="140.5" rx="4.2" ry="3.6"/>
                                    <ellipse cx="103" cy="139" rx="4.2" ry="3.6"/>
                                </g>

                                <g class="owl-tilt">
                                    <path d="M40 58C30 48 27 36 29 26c9 6 19 14 26 24Z" fill="#075985"/>
                                    <path d="M120 58c10-10 13-22 11-32-9 6-19 14-26 24Z" fill="#075985"/>
                                    <ellipse cx="80" cy="92" rx="52" ry="47" fill="url(#owl-g-body)"/>
                                    <ellipse cx="80" cy="110" rx="32" ry="27" fill="url(#owl-g-belly)"/>
                                    <path d="M66 104q4 4 8 0m4 0q4 4 8 0m4 0q4 4 8 0M72 114q4 4 8 0m4 0q4 4 8 0M76 124q4 4 8 0" stroke="#7dd3fc" stroke-width="2" fill="none" stroke-linecap="round"/>

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
                                    <circle class="owl-lid" cx="58" cy="74" r="21" fill="#0284c7" style="transform-origin: 58px 53px"/>
                                    <circle class="owl-lid" cx="102" cy="74" r="21" fill="#0284c7" style="transform-origin: 102px 53px"/>
                                    <path class="owl-brow owl-brow-l" d="M44 50q13-7 26-1" stroke="#0c4a6e" stroke-width="4" fill="none" stroke-linecap="round"/>
                                    <path class="owl-brow owl-brow-r" d="M90 49q13-6 26 1" stroke="#0c4a6e" stroke-width="4" fill="none" stroke-linecap="round"/>
                                    <ellipse class="owl-cheek" cx="49" cy="99" rx="5.5" ry="3.2" fill="#fb7185"/>
                                    <ellipse class="owl-cheek" cx="111" cy="99" rx="5.5" ry="3.2" fill="#fb7185"/>
                                    <ellipse class="owl-mouth" cx="80" cy="99" rx="4.5" ry="5" fill="#9a3412"/>
                                    <path d="M72 88q8-4 16 0l-6.5 11a1.8 1.8 0 0 1-3 0Z" fill="url(#owl-g-beak)" stroke="#d97706" stroke-width="1" stroke-linejoin="round"/>

                                    <g style="transform-origin: 80px 34px">
                                        <path d="M62 30h36v10q-18 6-36 0Z" fill="#1e293b"/>
                                        <polygon points="80,13 126,25 80,37 34,25" fill="#0f172a"/>
                                        <polygon points="80,15 116,24.5 80,22 44,24.5" fill="#fff" opacity=".08"/>
                                        <path d="M80 25l34 4v15" stroke="#fbbf24" stroke-width="2" fill="none" stroke-linecap="round"/>
                                        <path d="M111.5 43h5l1.5 8h-8Z" fill="#fbbf24"/>
                                        <circle cx="80" cy="25" r="2.6" fill="#fbbf24"/>
                                    </g>

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
                        <text class="owl-mark owl-q" x="130" y="40" fill="#0369a1" font-family="ui-sans-serif, system-ui, sans-serif" font-size="24" font-weight="800">?</text>
                        <text class="owl-mark owl-ex" x="132" y="40" fill="#f59e0b" font-family="ui-sans-serif, system-ui, sans-serif" font-size="24" font-weight="800">!</text>
                    </g>
                </svg>
            </div>

            <div class="flex flex-1 items-center justify-center px-6 py-24">
                <div class="w-full max-w-sm">
                    @yield('content')
                </div>
            </div>
        </div>
    </section>

    <script>
        // Shared owl helpers; pages add their own behaviour.
        window.Owl = (function () {
            var owl = document.getElementById('owl');
            var bubble = document.getElementById('owl-bubble');
            var pupils = owl.querySelectorAll('.owl-pupil');
            var timers = {};

            /* ---- Owl helpers ---- */
            function pick(list) { return list[Math.floor(Math.random() * list.length)]; }
            function play(cls, ms) {
                owl.classList.remove(cls);
                owl.getBoundingClientRect();
                owl.classList.add(cls);
                clearTimeout(timers[cls]);
                timers[cls] = setTimeout(function () { owl.classList.remove(cls); }, ms);
            }
            function setMood(name, ms) {
                clearTimeout(timers.mood);
                if (name) owl.setAttribute('data-mood', name); else owl.removeAttribute('data-mood');
                if (ms) timers.mood = setTimeout(function () { setMood(''); }, ms);
            }
            function say(text, ms) {
                bubble.textContent = text;
                bubble.classList.add('show');
                play('talk', 500);
                clearTimeout(timers.say);
                timers.say = setTimeout(function () { bubble.classList.remove('show'); }, ms || 2200);
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
            function lookAtEl(el) {
                var r = el.getBoundingClientRect();
                lookAt(r.left + r.width / 2, r.top + r.height / 2);
            }

            return { el: owl, pick: pick, play: play, setMood: setMood, say: say, lookAt: lookAt, lookAtEl: lookAtEl };
        })();
    </script>
    @stack('scripts')
</body>
</html>
