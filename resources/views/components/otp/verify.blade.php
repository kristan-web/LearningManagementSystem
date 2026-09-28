@extends('components.otp.layout')

@section('title', 'Verify Code')

@section('content')
    <h1 class="text-center text-3xl font-extrabold tracking-tight text-slate-900">
        Verify Your <span class="bg-gradient-to-r from-sky-500 to-blue-600 bg-clip-text text-transparent">Email</span>
    </h1>
    <p id="otp-hint" class="mt-3 text-center text-sm font-medium text-slate-500">
        Enter your email and we'll send you a 6-digit code.
    </p>

    <div id="otp-alert" class="mt-6 hidden rounded-xl border px-3.5 py-2.5 text-xs font-medium" role="alert"></div>

    {{-- Step 1: email --}}
    <form id="send-form" class="mt-8" novalidate>
        <label for="email" class="sr-only">Email</label>
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sky-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                    <path d="m3 7 9 6 9-6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <input type="email" id="email" name="email" value="{{ request('email') }}" required autofocus autocomplete="email" placeholder="Email"
                   class="block w-full rounded-xl border border-sky-200 bg-white py-3 pl-11 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-200/60">
        </div>
        <div class="mt-8 flex justify-center">
            <button type="submit" id="send-btn" class="w-40 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:from-sky-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-sky-300 active:scale-[.98] disabled:opacity-60">
                Send code
            </button>
        </div>
    </form>

    {{-- Step 2: code --}}
    <form id="verify-form" class="mt-8 hidden" novalidate>
        <fieldset>
            <legend class="sr-only">6-digit code</legend>
            <div id="otp-boxes" class="flex justify-center gap-2 sm:gap-3">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit {{ $i + 1 }}" {{ $i === 0 ? 'autocomplete=one-time-code' : 'autocomplete=off' }}
                           class="otp-digit h-12 w-11 sm:h-14 sm:w-12 rounded-xl border border-sky-200 bg-white text-center text-xl font-bold text-slate-900 shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-4 focus:ring-sky-200/60">
                @endfor
            </div>
        </fieldset>
        <div class="mt-8 flex justify-center">
            <button type="submit" id="verify-btn" class="w-40 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-500/30 transition hover:from-sky-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-sky-300 active:scale-[.98] disabled:opacity-60">
                Verify
            </button>
        </div>
        <p class="mt-8 text-center text-sm font-medium text-slate-500">
            Didn't get it?
            <button type="button" id="resend-btn" class="font-bold text-sky-700 underline underline-offset-2 hover:text-sky-900 disabled:no-underline disabled:text-slate-400">Resend</button>
            · <button type="button" id="change-email" class="font-bold text-sky-700 underline underline-offset-2 hover:text-sky-900">Change email</button>
        </p>
    </form>

    <p class="mt-8 text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-sky-700 underline underline-offset-4 hover:text-sky-900">Back to login</a>
    </p>
@endsection

@push('scripts')
    <script>
        (function () {
            var owl = Owl.el, pick = Owl.pick, play = Owl.play, setMood = Owl.setMood, say = Owl.say, lookAt = Owl.lookAt, lookAtEl = Owl.lookAtEl;
            var sendForm = document.getElementById('send-form');
            var verifyForm = document.getElementById('verify-form');
            var email = document.getElementById('email');
            var digits = Array.prototype.slice.call(document.querySelectorAll('.otp-digit'));
            var alertBox = document.getElementById('otp-alert');
            var hint = document.getElementById('otp-hint');
            var sendBtn = document.getElementById('send-btn');
            var verifyBtn = document.getElementById('verify-btn');
            var resendBtn = document.getElementById('resend-btn');
            var locked = false;
            var cooldown = null;
            var lastActive = Date.now();

            /* ---- Owl behaviour ---- */
            // Follows the cursor unless it is watching a field.
            document.addEventListener('pointermove', function (e) {
                lastActive = Date.now();
                owl.classList.remove('think');
                if (!locked) lookAt(e.clientX, e.clientY);
            });
            // Watches whichever field has focus; leans toward the digit box (left half / right half).
            document.addEventListener('focusin', function (e) {
                var i = digits.indexOf(e.target);
                locked = i > -1 || e.target === email;
                if (!locked) return;
                lookAtEl(e.target);
                owl.classList.toggle('lean-l', i > -1 && i < 3);
                owl.classList.toggle('lean-r', i > 2);
            });
            document.addEventListener('focusout', function () {
                setTimeout(function () {
                    if (digits.indexOf(document.activeElement) === -1 && document.activeElement !== email) {
                        locked = false;
                        owl.classList.remove('lean-l', 'lean-r');
                    }
                }, 0);
            });
            // Idle: blink, and rest a wing on its chin while waiting for the code.
            (function idle() {
                setTimeout(function () {
                    play('blinking', 250);
                    if (!verifyForm.classList.contains('hidden') && Date.now() - lastActive > 8000) owl.classList.add('think');
                    idle();
                }, 2500 + Math.random() * 3000);
            })();

            /* ---- UI helpers ---- */
            function showAlert(text, ok) {
                alertBox.textContent = text;
                alertBox.className = 'mt-6 rounded-xl border px-3.5 py-2.5 text-xs font-medium ' +
                    (ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-red-800');
            }
            function code() { return digits.map(function (d) { return d.value; }).join(''); }
            function clearDigits() { digits.forEach(function (d) { d.value = ''; }); digits[0].focus(); }
            function api(path, body) {
                return fetch('/api/v1/auth/otp/' + path, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(body)
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        if (res.status === 429) data.errors = ['Too many attempts. Please wait a minute.'];
                        data.ok = res.ok;
                        return data;
                    });
                });
            }
            function firstError(data) {
                if (data.errors) return Array.isArray(data.errors) ? data.errors[0] : Object.values(data.errors)[0][0];
                return data.message || 'Something went wrong.';
            }
            function startCooldown(sec) {
                clearInterval(cooldown);
                resendBtn.disabled = true;
                resendBtn.textContent = 'Resend (' + sec + 's)';
                cooldown = setInterval(function () {
                    if (--sec <= 0) { clearInterval(cooldown); resendBtn.disabled = false; resendBtn.textContent = 'Resend'; return; }
                    resendBtn.textContent = 'Resend (' + sec + 's)';
                }, 1000);
            }

            /* ---- Send code ---- */
            function send() {
                var value = email.value.trim();
                if (!email.checkValidity() || !value) {
                    showAlert('Please enter a valid email.');
                    setMood('confused', 2000);
                    say('Hmm, is that a real email?');
                    return Promise.resolve();
                }
                sendBtn.disabled = resendBtn.disabled = true;
                return api('send', { email: value }).then(function (data) {
                    sendBtn.disabled = false;
                    if (!data.ok) {
                        resendBtn.disabled = false;
                        showAlert(firstError(data));
                        setMood('sad', 2500);
                        play('shake', 500);
                        say('Oops, that didn\'t go through.');
                        return;
                    }
                    showAlert(data.message, true);
                    hint.textContent = 'Enter the 6-digit code sent to ' + value + '.';
                    sendForm.classList.add('hidden');
                    verifyForm.classList.remove('hidden');
                    clearDigits();
                    startCooldown(60);
                    play('flap', 1000);
                    say(pick(['Check your inbox! 📬', 'Code is on its way!']), 2400);
                });
            }
            sendForm.addEventListener('submit', function (e) { e.preventDefault(); send(); });
            resendBtn.addEventListener('click', send);
            document.getElementById('change-email').addEventListener('click', function () {
                verifyForm.classList.add('hidden');
                sendForm.classList.remove('hidden');
                alertBox.classList.add('hidden');
                hint.textContent = 'Enter your email and we\'ll send you a 6-digit code.';
                email.focus();
            });

            /* ---- Digit boxes ---- */
            digits.forEach(function (box, i) {
                box.addEventListener('input', function () {
                    box.value = box.value.replace(/\D/g, '').slice(-1);
                    if (!box.value) return;
                    play('nod', 250);
                    if (i < 5) digits[i + 1].focus();
                    var left = 6 - code().length;
                    if (left === 0) { say('All six! Let\'s check…', 1500); verifyForm.requestSubmit(); }
                    else if (left === 3) say('Halfway there!', 1200);
                });
                box.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !box.value && i > 0) digits[i - 1].focus();
                    if (e.key === 'ArrowLeft' && i > 0) digits[i - 1].focus();
                    if (e.key === 'ArrowRight' && i < 5) digits[i + 1].focus();
                });
                box.addEventListener('paste', function (e) {
                    var pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                    if (!pasted) return;
                    e.preventDefault();
                    digits.forEach(function (d, j) { d.value = pasted[j] || ''; });
                    digits[Math.min(pasted.length, 5)].focus();
                    setMood('surprised', 900);
                    play('hop', 500);
                    if (pasted.length === 6) verifyForm.requestSubmit();
                });
            });

            /* ---- Verify ---- */
            verifyForm.addEventListener('submit', function (e) {
                e.preventDefault();
                if (code().length !== 6) {
                    setMood('confused', 1500);
                    say('I need all 6 digits!');
                    return;
                }
                verifyBtn.disabled = true;
                api('verify', { email: email.value.trim(), code: code() }).then(function (data) {
                    verifyBtn.disabled = false;
                    if (!data.ok) {
                        showAlert(firstError(data));
                        setMood('sad', 2500);
                        play('shake', 500);
                        say(data.reason === 'expired' ? 'That one expired… resend?' : 'Not quite, try again!');
                        clearDigits();
                        return;
                    }
                    showAlert(data.message, true);
                    digits.forEach(function (d) { d.disabled = true; });
                    verifyBtn.disabled = resendBtn.disabled = true;
                    setMood('happy');
                    play('jump', 800);
                    play('flap', 1000);
                    say('Verified! Hoo-ray! 🎉', 3000);
                    // Token goes in the hash so it never reaches server logs.
                    setTimeout(function () { location.href = @json(route('otp.reset')) + '#' + data.reset_token; }, 1400);
                });
            });

            // Greeting
            setTimeout(function () { play('wave', 1000); say('Let\'s verify it\'s you!', 2200); }, 600);
        })();
    </script>
@endpush
