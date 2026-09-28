@extends('components.otp.layout')

@section('title', 'Reset Password')

@section('content')
    <h1 class="text-center text-3xl font-extrabold tracking-tight text-slate-900">
        Set a New <span class="bg-gradient-to-r from-sky-500 to-blue-600 bg-clip-text text-transparent">Password</span>
    </h1>
    <p class="mt-3 text-center text-sm font-medium text-slate-500">
        Choose a strong password you haven't used before.
    </p>

    <div id="reset-alert" class="mt-6 hidden rounded-xl border px-3.5 py-2.5 text-xs font-medium" role="alert"></div>

    <form id="reset-form" class="mt-8" novalidate>
        <div class="space-y-4">
            @foreach (['password' => 'New password', 'password_confirmation' => 'Confirm password'] as $name => $label)
                <div>
                    <label for="{{ $name }}" class="sr-only">{{ $label }}</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sky-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                @if ($name === 'password')
                                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                                    <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke-linecap="round"/>
                                @else
                                    <path d="M12 3 4 6v6c0 4.5 3.4 8.3 8 9 4.6-.7 8-4.5 8-9V6l-8-3Z" stroke-linejoin="round"/>
                                    <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                                @endif
                            </svg>
                        </span>
                        <input type="password" id="{{ $name }}" name="{{ $name }}" placeholder="{{ $label }}" required minlength="8" autocomplete="new-password" {{ $name === 'password' ? 'autofocus' : '' }}
                               class="pw-input block w-full rounded-xl border border-sky-200 bg-white py-3 pl-11 pr-12 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-200/60">
                        <button type="button" data-toggle="{{ $name }}" class="pw-toggle absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-sky-600 focus:outline-none focus-visible:text-sky-600" aria-label="Show password" aria-pressed="false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Strength meter --}}
        <div class="mt-4" aria-live="polite">
            <div class="flex gap-1.5">
                @for ($i = 0; $i < 4; $i++)
                    <span class="strength-bar h-1.5 flex-1 rounded-full bg-slate-200 transition-colors duration-300"></span>
                @endfor
            </div>
            <ul class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1 text-xs font-medium text-slate-400">
                <li data-rule="len">• 8+ characters</li>
                <li data-rule="case">• Upper &amp; lowercase</li>
                <li data-rule="num">• A number</li>
                <li data-rule="sym">• A symbol</li>
            </ul>
            <p id="match-hint" class="mt-2 hidden text-xs font-medium"></p>
        </div>

        <div class="mt-8 flex justify-center">
            <button type="submit" id="reset-btn" class="w-48 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:from-sky-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-sky-300 active:scale-[.98] disabled:opacity-60">
                Reset password
            </button>
        </div>
    </form>

    <p class="mt-8 text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-900">Back to login</a>
    </p>
@endsection

@push('scripts')
    <script>
        (function () {
            var owl = Owl.el, pick = Owl.pick, play = Owl.play, setMood = Owl.setMood, say = Owl.say, lookAt = Owl.lookAt, lookAtEl = Owl.lookAtEl;
            var form = document.getElementById('reset-form');
            var pw = document.getElementById('password');
            var confirm = document.getElementById('password_confirmation');
            var inputs = [pw, confirm];
            var toggles = document.querySelectorAll('.pw-toggle');
            var bars = document.querySelectorAll('.strength-bar');
            var matchHint = document.getElementById('match-hint');
            var alertBox = document.getElementById('reset-alert');
            var btn = document.getElementById('reset-btn');
            var token = location.hash.slice(1);
            var lastScore = 0, wasMatch = false, wasOnPw = false;

            function showAlert(text, ok) {
                alertBox.textContent = text;
                alertBox.className = 'mt-6 rounded-xl border px-3.5 py-2.5 text-xs font-medium ' +
                    (ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-red-800');
            }

            // No ticket from the OTP page: send the user back to get one.
            if (!/^[A-Za-z0-9]{64}$/.test(token)) {
                showAlert('Please verify your email first.');
                form.querySelectorAll('input, button').forEach(function (el) { el.disabled = true; });
                setMood('confused');
                say('Verify your email first!', 4000);
                setTimeout(function () { location.href = @json(route('otp')); }, 2500);
                return;
            }
            history.replaceState(null, '', location.pathname); // drop token from the address bar

            /* ---- Owl: follows the cursor, covers its eyes while you type a password ---- */
            function onPw() { return inputs.indexOf(document.activeElement) > -1 || [].indexOf.call(toggles, document.activeElement) > -1; }
            function update() {
                var focused = onPw();
                var shown = pw.type === 'text';
                owl.classList.toggle('cover', focused && !shown);
                owl.classList.toggle('peek', focused && shown);
                if (inputs.indexOf(document.activeElement) > -1) lookAtEl(document.activeElement);
                if (focused && !wasOnPw && !shown) say(pick(["I won't peek!", 'Eyes closed, go ahead!']), 1600);
                wasOnPw = focused;
            }
            document.addEventListener('pointermove', function (e) { if (!onPw()) lookAt(e.clientX, e.clientY); });
            document.addEventListener('focusin', update);
            document.addEventListener('focusout', function () { setTimeout(update, 0); });

            /* ---- Show/hide (both fields together, like one secret) ---- */
            toggles.forEach(function (t) {
                t.addEventListener('click', function () {
                    var show = pw.type === 'password';
                    inputs.forEach(function (i) { i.type = show ? 'text' : 'password'; });
                    toggles.forEach(function (x) {
                        x.setAttribute('aria-pressed', show);
                        x.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    });
                    update();
                    say(show ? 'Just a tiny peek… 👀' : 'Okay, eyes closed!', 1500);
                });
            });

            /* ---- Strength + match ---- */
            var rules = {
                len: function (v) { return v.length >= 8; },
                case: function (v) { return /[a-z]/.test(v) && /[A-Z]/.test(v); },
                num: function (v) { return /\d/.test(v); },
                sym: function (v) { return /[^A-Za-z0-9]/.test(v); }
            };
            var colors = ['bg-rose-400', 'bg-amber-400', 'bg-sky-400', 'bg-emerald-500'];

            function check() {
                var v = pw.value, score = 0;
                Object.keys(rules).forEach(function (k) {
                    var ok = rules[k](v);
                    score += ok;
                    var li = document.querySelector('[data-rule="' + k + '"]');
                    li.classList.toggle('text-emerald-600', ok);
                    li.classList.toggle('text-slate-400', !ok);
                });
                bars.forEach(function (b, i) {
                    b.classList.remove('bg-slate-200', 'bg-rose-400', 'bg-amber-400', 'bg-sky-400', 'bg-emerald-500');
                    b.classList.add(i < score && v ? colors[score - 1] : 'bg-slate-200');
                });
                if (score === 4 && lastScore < 4) { setMood('happy', 1400); play('hop', 600); say('Super strong! 💪', 1600); }
                lastScore = score;

                var match = confirm.value && confirm.value === v;
                matchHint.textContent = match ? '✓ Passwords match' : '✗ Passwords don’t match yet';
                matchHint.className = 'mt-2 text-xs font-medium ' + (confirm.value ? (match ? 'text-emerald-600' : 'text-rose-500') : 'hidden');
                if (match && !wasMatch) { play('nod', 250); say('They match!', 1200); }
                wasMatch = match;
                return score;
            }
            inputs.forEach(function (i) { i.addEventListener('input', function () { play('nod', 250); check(); }); });

            /* ---- Submit ---- */
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (pw.value.length < 8) { showAlert('Password must be at least 8 characters.'); setMood('confused', 1800); say('A bit longer, please!'); return; }
                if (pw.value !== confirm.value) { showAlert('Passwords do not match.'); setMood('confused', 1800); play('shake', 500); say('Those two don’t match!'); return; }

                btn.disabled = true;
                fetch('/api/v1/auth/otp/reset', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ token: token, password: pw.value, password_confirmation: confirm.value })
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        if (res.status === 429) data.errors = ['Too many attempts. Please wait a minute.'];
                        if (!res.ok) {
                            btn.disabled = false;
                            var err = data.errors ? (Array.isArray(data.errors) ? data.errors[0] : Object.values(data.errors)[0][0]) : 'Something went wrong.';
                            showAlert(err);
                            setMood('sad', 2500);
                            play('shake', 500);
                            say('Oops, that didn’t work.');
                            if (data.reason === 'expired') setTimeout(function () { location.href = @json(route('otp')); }, 2500);
                            return;
                        }
                        showAlert(data.message, true);
                        form.querySelectorAll('input, button').forEach(function (el) { el.disabled = true; });
                        owl.classList.remove('cover', 'peek');
                        setMood('happy');
                        play('jump', 800);
                        play('flap', 1000);
                        say('All done! Welcome back 🎉', 3000);
                        setTimeout(function () { location.href = @json(route('login')); }, 2200);
                    });
                });
            });

            // Blink now and then
            (function idle() { setTimeout(function () { play('blinking', 250); idle(); }, 2500 + Math.random() * 3000); })();
            setTimeout(function () { play('wave', 1000); say('Almost there! New password time.', 2400); }, 600);
        })();
    </script>
@endpush
