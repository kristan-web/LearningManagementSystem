{{-- Registrar and cashier logins, ported from EnrollmentMS
     (app/Registrar/View/index.php, app/Accounting/View/index.php).
     $portal is 'registrar' or 'cashier' (see routes/web.php). --}}
@php
    $isCashier = $portal === 'cashier';
    $tag = $isCashier ? 'Cashier & Accounting' : 'Admission & Registrar';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}" />
  <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}" />
  <title>{{ $isCashier ? 'Cashier' : 'Registrar' }} Login &middot; {{ $tag }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/staff/portal.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/staff/staff-login.css') }}" />
</head>
<body>
  <div class="blob blob--1"></div>
  <div class="blob blob--3"></div>
  <div class="blob blob--5"></div>

  <div class="wrap wrap--status">
    <header class="topbar">
      <a class="topbar__brand" href="{{ url()->current() }}" aria-label="{{ $isCashier ? 'Cashier' : 'Registrar' }} login">
        <img class="topbar__logo" src="{{ asset('images/logo.png') }}" alt="School crest" />
        <span class="topbar__name">
          <strong>Enrollment Management System</strong>
          <span class="topbar__tag {{ $isCashier ? 'topbar__tag--cashier' : '' }}">{{ $tag }}</span>
        </span>
      </a>

      <div class="topbar__actions">
        <a class="topbar__cta" href="{{ route('landing') }}">
          <span>Student Admission</span>
        </a>
      </div>
    </header>

    <main class="login">
      <div class="brand">
        <img class="brand__logo" src="{{ asset('images/logo.png') }}" alt="Enrollment Management System crest" />
      </div>

      <h1 class="login__title">{{ $isCashier ? 'Cashier' : 'Registrar' }} Login</h1>
      <p class="login__subtitle">Sign in with your staff account to {{ $isCashier ? 'record payments' : 'review admissions' }}</p>

      <form class="form" id="loginForm" method="POST" action="{{ route('login.authenticate') }}" novalidate>
        @csrf
        <div class="field" id="identifierField">
          <label class="field__inner">
            <span class="field__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            </span>
            <span class="field__label">Email:</span>
            <input type="email" name="identifier" value="{{ old('identifier') }}" autocomplete="username" required @error('identifier') class="is-invalid" @enderror />
          </label>
        </div>

        <div class="field" id="passwordField">
          <div class="field__inner" id="pwWrap">
            <span class="field__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
            </span>
            <span class="field__label">Password:</span>
            <input type="password" name="password" autocomplete="current-password" required aria-label="Password" />
            <button type="button" class="field__toggle" id="pwToggle" aria-label="Show password" aria-pressed="false">
              <svg class="pw__eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="pw__eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 8 10 8a13.2 13.2 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.5 13.5 0 0 0 2 12s3 7 10 7a9.7 9.7 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn--submit btn--compact" id="loginSubmit">Log In</button>
        <p class="form-msg {{ $errors->any() ? 'is-error' : '' }}" id="loginMsg">{{ $errors->first() }}</p>
      </form>

      <nav class="portal-nav" aria-label="Other staff portals">
        <span class="portal-nav__label">Other staff portals</span>
        <div class="portal-nav__grid">
          <a class="portal-link portal-link--registrar" href="{{ route('login') }}">
            <span class="portal-link__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>
            </span>
            <span class="portal-link__text">
              <strong>Admin</strong>
              <span>Admissions &amp; enrollment</span>
            </span>
            <svg class="portal-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </a>

          <a class="portal-link portal-link--accounting" href="{{ route($isCashier ? 'registrar.login' : 'cashier.login') }}">
            <span class="portal-link__icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M6 15h4"/></svg>
            </span>
            <span class="portal-link__text">
              <strong>{{ $isCashier ? 'Registrar' : 'Cashier' }}</strong>
              <span>{{ $isCashier ? 'Admissions & records' : 'Payments & receipts' }}</span>
            </span>
            <svg class="portal-link__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          </a>
        </div>
      </nav>
    </main>
  </div>

  <footer class="footer">&copy; 2026 Enrollment Management System &middot; {{ $tag }}</footer>

  <script src="{{ asset('assets/staff/staff-login.js') }}"></script>
</body>
</html>
