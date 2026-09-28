// staff-login.js — drives the registrar and cashier logins (auth/staff-login.blade.php).
// Ported from EnrollmentMS registrar-login.js / cashier-login.js. The form now does a
// normal POST to Laravel's login route; the server re-checks and flashes errors back.
(function () {
  "use strict";

  var loginForm = document.getElementById("loginForm");
  var loginMsg = document.getElementById("loginMsg");
  var submitBtn = document.getElementById("loginSubmit");
  var pwWrap = document.getElementById("pwWrap");
  var pwToggle = document.getElementById("pwToggle");

  function setMsg(text, type) {
    loginMsg.textContent = text || "";
    loginMsg.classList.remove("is-error", "is-success");
    if (type) loginMsg.classList.add(type);
  }

  // Show / hide the password.
  pwToggle.addEventListener("click", function () {
    var input = pwWrap.querySelector("input");
    var reveal = input.type === "password";
    input.type = reveal ? "text" : "password";
    pwWrap.classList.toggle("is-shown", reveal);
    pwToggle.setAttribute("aria-label", reveal ? "Hide password" : "Show password");
    pwToggle.setAttribute("aria-pressed", reveal ? "true" : "false");
    input.focus();
  });

  loginForm.addEventListener("submit", function (e) {
    var identifierEl = loginForm.elements.identifier;
    var passwordEl = loginForm.elements.password;
    var email = identifierEl.value.trim();

    identifierEl.classList.remove("is-invalid");
    passwordEl.classList.remove("is-invalid");

    // "All forms have been filled out?" — client-side gate (the server re-checks).
    if (!email || !passwordEl.value) {
      e.preventDefault();
      if (!email) identifierEl.classList.add("is-invalid");
      if (!passwordEl.value) passwordEl.classList.add("is-invalid");
      return setMsg("Please enter your email and your password.", "is-error");
    }

    setMsg("");
    submitBtn.disabled = true;
    submitBtn.textContent = "Signing in…";
  });
})();
