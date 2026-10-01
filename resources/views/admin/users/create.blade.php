{{-- Admin: Create User (layout unchanged; the <style> block is the navy visual layer. The script relies on the ids/classes.) --}}
@extends('layouts.admin')
@section('title', 'Create Account')

@section('styles')
    <style>
        /* Same form layout as before; visual layer only (navy "official form" look). Class names below are also used by the script. */
        .create-form-container { max-width: 72rem; margin: 0 auto; }

        /* Header */
        .chart-center { padding-top: 2rem; padding-bottom: 1rem; text-align: start; max-width: 72rem; margin: 0 auto 1rem auto; width: 100%; }
        .ca-eyebrow { display: inline-flex; align-items: center; gap: .5rem; font-size: .6875rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #c85a08; }
        .ca-eyebrow::before { content: ''; width: 1.25rem; height: 2px; border-radius: 2px; background: currentColor; }
        html.dark .ca-eyebrow { color: #f4b301; }

        /* Sections: navy band header with dot texture + gold hairline */
        .form-section {
            position: relative; overflow: hidden; margin-bottom: 1.5rem; border: 1px solid #e3e8f4; border-radius: 1.25rem; background: #fff;
            box-shadow: 0 1px 2px rgb(22 36 79 / .04), 0 18px 40px -24px rgb(22 36 79 / .35);
        }
        .form-section h3 {
            position: relative; display: flex; align-items: center; gap: .75rem; margin: 0; padding: 1.05rem 1.75rem;
            background-image: radial-gradient(rgb(255 255 255 / .10) 1px, transparent 1px), linear-gradient(158deg, #24386f, #16244f 58%, #0f1a38);
            background-size: 16px 16px, auto; color: #fff;
            font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; font-size: 1.0625rem; font-weight: 700; letter-spacing: -.01em;
        }
        .form-section h3::before { content: ''; width: .5rem; height: .5rem; flex: none; border-radius: 9999px; background: #f4b301; box-shadow: 0 0 0 4px rgb(244 179 1 / .22); }
        .form-section h3::after { content: ''; position: absolute; inset: auto 0 0 0; height: 2px; background: linear-gradient(90deg, transparent, #f4b301 30%, #f4b301 70%, transparent); opacity: .75; }
        html.dark .form-section { background: #0f1a38; border-color: #24386f; box-shadow: none; }

        .create-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 1.5rem; padding: 1.5rem 1.75rem .5rem; }
        .create-form-grid .full-width { grid-column: 1 / -1; }
        @media (max-width: 640px) {
            .create-form-grid { grid-template-columns: 1fr; padding: 1.25rem 1.25rem .25rem; }
            .create-form-grid .full-width { grid-column: span 1; }
            .form-section h3 { padding: 1rem 1.25rem; }
        }

        /* Fields: tinted fill, small caps labels, navy focus */
        .form-group { margin-bottom: 1.25rem; }
        .form-group > label { display: block; margin-bottom: .45rem; font-size: .6875rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: rgb(22 36 79 / .6); }
        .form-group > label.required::after { content: ' *'; color: #e11d48; }
        .form-group input, .form-group select {
            width: 100%; height: 2.875rem; padding: 0 .95rem; border: 1.5px solid transparent; border-radius: .8rem;
            background: #f2f5fb; color: #16244f; font-size: .9rem; font-weight: 500;
            transition: background-color .15s, border-color .15s, box-shadow .15s;
        }
        .form-group input:hover, .form-group select:hover { background: #ecf0f9; }
        .form-group input:focus, .form-group select:focus { outline: none; background: #fff; border-color: #2f5fd0; box-shadow: 0 0 0 4px rgb(47 95 208 / .13); }
        .form-group input::placeholder { color: #9aa3b8; font-weight: 400; }
        .form-group .error-text { display: flex; align-items: center; gap: .35rem; margin-top: .4rem; font-size: .75rem; font-weight: 600; color: #e11d48; }
        .form-group .error-text::before { content: '!'; display: inline-flex; height: 1rem; width: 1rem; align-items: center; justify-content: center; border-radius: 9999px; background: #e11d48; color: #fff; font-size: .625rem; font-weight: 800; }
        html.dark .form-group > label { color: #94a3b8; }
        html.dark .form-group input, html.dark .form-group select { background: rgb(255 255 255 / .05); color: #f8fafc; }
        html.dark .form-group input:hover, html.dark .form-group select:hover { background: rgb(255 255 255 / .08); }
        html.dark .form-group input:focus, html.dark .form-group select:focus { background: #0f172a; border-color: #60a5fa; box-shadow: 0 0 0 4px rgb(96 165 250 / .2); }
        html.dark .form-group .error-text { color: #fb7185; }

        /* Password toggle, strength and match (class names are set by the script) */
        .password-wrapper, .confirm-password-wrapper { position: relative; }
        .password-wrapper input, .confirm-password-wrapper input { padding-right: 3rem; }
        .password-toggle, .confirm-password-toggle {
            position: absolute; right: .6rem; top: 50%; transform: translateY(-50%); display: flex; padding: .35rem; border: none; border-radius: .5rem;
            background: none; color: rgb(22 36 79 / .45); cursor: pointer; transition: background-color .15s, color .15s;
        }
        .password-toggle:hover, .confirm-password-toggle:hover { background: rgb(47 95 208 / .1); color: #2f5fd0; }
        .password-strength { height: 5px; margin-top: .5rem; overflow: hidden; border-radius: 9999px; background: #e8ecf5; }
        .password-strength-bar { height: 100%; width: 0%; border-radius: 9999px; transition: width .3s, background-color .3s; }
        .password-strength-bar.weak { width: 33%; background-color: #ef4444; }
        .password-strength-bar.medium { width: 66%; background-color: #f59e0b; }
        .password-strength-bar.strong { width: 100%; background: linear-gradient(90deg, #10b981, #059669); }
        .password-strength-text { display: block; margin-top: .3rem; font-size: .6875rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .password-strength-text.weak { color: #ef4444; }
        .password-strength-text.medium { color: #d97706; }
        .password-strength-text.strong { color: #059669; }
        .password-match-msg { display: block; margin-top: .45rem; font-size: .75rem; font-weight: 700; }
        .password-match-msg.match { color: #059669; }
        .password-match-msg.no-match { color: #e11d48; }
        html.dark .password-strength { background: rgb(255 255 255 / .1); }
        html.dark .password-toggle, html.dark .confirm-password-toggle { color: #94a3b8; }

        /* Role: segmented control (script toggles .selected on .role-option) */
        .role-selector { display: flex; gap: .3rem; height: 2.875rem; padding: .3rem; border-radius: .9rem; background: #f2f5fb; }
        .role-option { flex: 1; border-radius: .65rem; transition: background-color .15s, box-shadow .15s; }
        .role-option:hover { background: rgb(255 255 255 / .7); }
        .role-option input[type="radio"] { display: none; }
        .role-option label {
            display: flex; height: 100%; align-items: center; justify-content: center; gap: .45rem; margin: 0; cursor: pointer;
            font-size: .875rem; font-weight: 600; color: rgb(22 36 79 / .6);
        }
        .role-option label svg { width: 1rem; height: 1rem; flex: none; }
        .role-option.selected { background-image: linear-gradient(180deg, rgb(255 255 255 / .14), rgb(255 255 255 / 0) 55%), linear-gradient(150deg, #3a52a0, #16244f 78%); box-shadow: inset 0 1px 0 rgb(255 255 255 / .2), 0 6px 14px -6px rgb(22 36 79 / .6); }
        .role-option.selected label { color: #fff; }
        .role-option.selected label svg { color: #f4b301; }
        html.dark .role-selector { background: rgb(255 255 255 / .05); }
        html.dark .role-option:hover { background: rgb(255 255 255 / .06); }
        html.dark .role-option label { color: #94a3b8; }
        html.dark .role-option.selected label { color: #fff; }

        #student-fields, #teacher-fields { display: none; }
        #student-fields.active, #teacher-fields.active { display: block; animation: ca-reveal .25s ease; }
        @keyframes ca-reveal { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

        /* Buttons */
        .btn-cancel {
            display: inline-flex; min-height: 2.625rem; align-items: center; justify-content: center; padding: .625rem 1.25rem;
            border: 1px solid #d5dcec; border-radius: .75rem; background: #fff; color: #455072; font-size: .875rem; font-weight: 600;
            cursor: pointer; transition: background-color .15s, color .15s, border-color .15s;
        }
        .btn-cancel:hover { background: #f0f4fd; border-color: #c9d6f5; color: #1e46a8; }
        html.dark .btn-cancel { background: #1e293b; border-color: #475569; color: #e2e8f0; }
        html.dark .btn-cancel:hover { background: rgb(59 130 246 / .12); color: #fff; }

        /* Review dialog (rows are built by the script) */
        .confirm-create-modal {
            position: fixed; inset: 0; margin: auto; width: min(36rem, calc(100% - 2rem)); max-height: calc(100vh - 2rem); padding: 0; overflow: hidden;
            border: 1px solid #e3e8f4; border-radius: 1.25rem; background: #fff; box-shadow: 0 24px 60px -12px rgb(22 36 79 / .35);
        }
        .confirm-create-modal::backdrop { background: rgb(22 36 79 / .45); backdrop-filter: blur(4px); }
        .confirm-create-modal__content { padding: 0 1.5rem 1.5rem; }
        .confirm-create-modal__header {
            display: flex; align-items: center; justify-content: space-between; margin: 0 -1.5rem 1rem; padding: 1.05rem 1.5rem;
            background-image: radial-gradient(rgb(255 255 255 / .10) 1px, transparent 1px), linear-gradient(158deg, #24386f, #16244f 58%, #0f1a38); background-size: 16px 16px, auto;
            border-bottom: 2px solid #f4b301;
        }
        .confirm-create-modal__title { margin: 0; font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; font-size: 1.125rem; font-weight: 700; color: #fff; }
        .confirm-create-modal__close { display: flex; height: 2rem; width: 2rem; align-items: center; justify-content: center; border: 0; border-radius: 9999px; background: rgb(255 255 255 / .1); color: #fff; font-size: 1.4rem; line-height: 1; cursor: pointer; }
        .confirm-create-modal__close:hover { background: rgb(255 255 255 / .2); }
        .confirm-create-modal__body { max-height: 50vh; overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none; }
        .confirm-create-modal__body::-webkit-scrollbar { display: none; }
        .confirm-create-modal__row { display: flex; justify-content: space-between; gap: 1rem; padding: .55rem .25rem; border-bottom: 1px dashed #e3e8f4; }
        .confirm-create-modal__label { color: #737c95; font-size: .8125rem; font-weight: 500; }
        .confirm-create-modal__value { max-width: 60%; color: #16244f; font-size: .875rem; font-weight: 600; text-align: right; word-break: break-word; }
        .confirm-create-modal__section-title { margin: 1rem 0 .25rem; color: #c85a08; font-size: .6875rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .confirm-create-modal__actions { display: flex; justify-content: flex-end; gap: .75rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eef1f8; }
        html.dark .confirm-create-modal { background: #0f1a38; border-color: #24386f; }
        html.dark .confirm-create-modal__actions { border-color: rgb(255 255 255 / .1); }
        html.dark .confirm-create-modal__value { color: #fff; }
        html.dark .confirm-create-modal__label { color: #94a3b8; }
        html.dark .confirm-create-modal__row { border-color: rgb(255 255 255 / .1); }
        html.dark .confirm-create-modal__section-title { color: #f4b301; }
    </style>
@endsection

@section('content')
    <div class="create-form-container">
        <div class="flex items-center justify-between mb-4">
            <div class="chart-center">
                <p class="ca-eyebrow">User Management</p>
                <h1 class="mt-1 font-display text-[1.75rem] font-bold tracking-[-.5px] text-ink dark:text-white">Create Account</h1>
                <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Create a new user account. You'll review every detail before it's saved.</p>
            </div>
            <!-- <a href="{{ route('admin.users.index') }}" class="btn-cancel">Back</a> -->
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" id="createAccountForm">
            @csrf

            {{-- Basic Account Info --}}
            <div class="form-section">
                <h3>Account Information</h3>
                <div class="create-form-grid">
                    <div class="form-group">
                        <label class="required" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                        @error('first_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                        @error('last_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name') }}">
                        @error('middle_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" autocomplete="new-password" id="password" name="password" required minlength="8" placeholder="Enter password">
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                                <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.522 3 12 3s8.268 4.943 9.542 9c-1.274 4.057-5.064 9-9.542 9S3.732 16.057 2.458 12z" /></svg>
                                <svg class="w-5 h-5 eye-close" fill="none" stroke="currentColor" viewbox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l5.858 5.858M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <span class="password-strength-text" id="passwordStrengthText"></span>
                        @error('password')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="password_confirmation">Confirm Password</label>
                        <div class="confirm-password-wrapper">
                            <input type="password" autocomplete="new-password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Confirm password">
                            <button type="button" class="confirm-password-toggle" id="confirmPasswordToggle" aria-label="Toggle confirm password visibility">
                                <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.522 3 12 3s8.268 4.943 9.542 9c-1.274 4.057-5.064 9-9.542 9S3.732 16.057 2.458 12z" /></svg>
                                <svg class="w-5 h-5 eye-close" fill="none" stroke="currentColor" viewbox="0 0 24 24" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l5.858 5.858M9.878 9.878L3 3m6.878 6.878L21 21" /></svg>
                            </button>
                        </div>
                        <span class="password-match-msg" id="passwordMatchMsg"></span>
                        @error('password_confirmation')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="role">Role</label>
                        <div class="role-selector">
                            <div class="role-option {{ old('role') === 'Student' ? 'selected' : '' }}" id="role-option-student">
                                <input type="radio" id="role_student" name="role" value="Student" {{ old('role') === 'Student' ? 'checked' : '' }}>
                                <label for="role_student"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5Zm0 0 6.16-3.422a12.083 12.083 0 0 1 .665 6.479A11.952 11.952 0 0 0 12 20.055a11.952 11.952 0 0 0-6.824-2.998 12.078 12.078 0 0 1 .665-6.479L12 14Z"/></svg>Student</label>
                            </div>
                            <div class="role-option {{ old('role') === 'Teacher' ? 'selected' : '' }}" id="role-option-teacher">
                                <input type="radio" id="role_teacher" name="role" value="Teacher" {{ old('role') === 'Teacher' ? 'checked' : '' }}>
                                <label for="role_teacher"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>Teacher</label>
                            </div>
                            <div class="role-option {{ in_array(old('role'), ['Admin','Staff','Registrar','Accounting']) ? 'selected' : '' }}" id="role-option-admin">
                                <input type="radio" id="role_admin" name="role" value="Admin" {{ old('role') === 'Admin' ? 'checked' : '' }}>
                                <label for="role_admin"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016Z"/></svg>Admin</label>
                            </div>
                        </div>
                        @error('role')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Active" {{ old('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Suspended" {{ old('status') === 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="Locked" {{ old('status') === 'Locked' ? 'selected' : '' }}>Locked</option>
                        </select>
                        @error('status')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <input type="hidden" name="is_deleted" value="0">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">-- Select --</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate') }}">
                        @error('birthdate')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" maxlength="20">
                        @error('contact_number')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}">
                        @error('address')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Student Fields --}}
            <div class="form-section" id="student-fields">
                <h3>Student Details</h3>
                <div class="create-form-grid">
                    <div class="form-group">
                        <label for="student_lrn">LRN</label>
                        <input type="text" id="student_lrn" name="student_lrn" value="{{ old('student_lrn') }}" maxlength="12">
                        @error('student_lrn')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="student_number">Student Number</label>
                        <input type="text" id="student_number" name="student_number" value="{{ old('student_number') }}" maxlength="20">
                        @error('student_number')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="grade_level">Grade Level</label>
                        <select id="grade_level" name="grade_level">
                            <option value="11" {{ old('grade_level') === '11' ? 'selected' : '' }}>Grade 11</option>
                            <option value="12" {{ old('grade_level') === '12' ? 'selected' : '' }}>Grade 12</option>
                        </select>
                        @error('grade_level')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="student_guardian_id">Guardian ID</label>
                        <input type="number" id="student_guardian_id" name="student_guardian_id" value="{{ old('student_guardian_id') }}">
                        @error('student_guardian_id')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Teacher Fields --}}
            <div class="form-section" id="teacher-fields">
                <h3>Teacher Details</h3>
                <div class="create-form-grid">
                    <div class="form-group">
                        <label for="teacher_number">Teacher Number</label>
                        <input type="text" id="teacher_number" name="teacher_number" value="{{ old('teacher_number') }}" maxlength="20">
                        @error('teacher_number')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" value="{{ old('specialization') }}" placeholder="e.g., Mathematics, Science, English, etc.">
                        @error('specialization')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('admin.users.index') }}" class="btn-cancel">Cancel</a>
                <button type="button" id="reviewBtn" class="btn-navy">
                    <svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                    Review & Create Account
                </button>
            </div>
        </form>

        {{-- Confirmation Modal --}}
        <dialog class="confirm-create-modal" id="confirmCreateModal">
            <div class="confirm-create-modal__content">
                <div class="confirm-create-modal__header">
                    <h2 class="confirm-create-modal__title">Review Account Details</h2>
                    <button type="button" class="confirm-create-modal__close" id="confirmCreateClose">&times;</button>
                </div>
                <div class="confirm-create-modal__body" id="confirmCreateBody">
                    {{-- Populated by JS --}}
                </div>
                <div class="confirm-create-modal__actions">
                    <button type="button" class="btn-cancel" id="confirmCreateCancel">Back to Edit</button>
                    <button type="button" class="btn-navy" id="confirmCreateConfirm">
                        <svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Confirm & Create
                    </button>
                </div>
            </div>
        </dialog>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleInputs = document.querySelectorAll('input[name="role"]');
            const studentFields = document.getElementById('student-fields');
            const teacherFields = document.getElementById('teacher-fields');

            function toggleFields() {
                const selectedRole = document.querySelector('input[name="role"]:checked')?.value || '';
                studentFields.classList.toggle('active', selectedRole === 'Student');
                teacherFields.classList.toggle('active', selectedRole === 'Teacher');

                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('selected'));
                if (selectedRole === 'Student') document.getElementById('role-option-student').classList.add('selected');
                if (selectedRole === 'Teacher') document.getElementById('role-option-teacher').classList.add('selected');
                if (selectedRole === 'Admin' || selectedRole === 'Staff' || selectedRole === 'Registrar' || selectedRole === 'Accounting') {
                    document.getElementById('role-option-admin').classList.add('selected');
                }
            }

            roleInputs.forEach(input => input.addEventListener('change', toggleFields));
            toggleFields();

            {{-- Password Toggle & Strength Indicator --}}
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
            const passwordStrengthBar = document.getElementById('passwordStrengthBar');
            const passwordStrengthText = document.getElementById('passwordStrengthText');
            const passwordMatchMsg = document.getElementById('passwordMatchMsg');

            if (passwordToggle) {
                passwordToggle.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClose = this.querySelector('.eye-close');
                    eyeOpen.style.display = isPassword ? 'none' : 'block';
                    eyeClose.style.display = isPassword ? 'block' : 'none';
                });
            }

            if (confirmPasswordToggle) {
                confirmPasswordToggle.addEventListener('click', function() {
                    const isPassword = confirmPasswordInput.type === 'password';
                    confirmPasswordInput.type = isPassword ? 'text' : 'password';
                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClose = this.querySelector('.eye-close');
                    eyeOpen.style.display = isPassword ? 'none' : 'block';
                    eyeClose.style.display = isPassword ? 'block' : 'none';
                });
            }

            function checkPasswordStrength(password) {
                let score = 0;
                if (password.length >= 8) score++;
                if (password.length >= 12) score++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
                if (/\d/.test(password)) score++;
                if (/[^a-zA-Z0-9]/.test(password)) score++;

                return score;
            }

            function updatePasswordStrength(password) {
                if (!password) {
                    passwordStrengthBar.className = 'password-strength-bar';
                    passwordStrengthText.textContent = '';
                    passwordStrengthText.className = 'password-strength-text';
                    return;
                }
                const score = checkPasswordStrength(password);
                if (score <= 2) {
                    passwordStrengthBar.className = 'password-strength-bar weak';
                    passwordStrengthText.textContent = 'Weak';
                    passwordStrengthText.className = 'password-strength-text weak';
                } else if (score <= 3) {
                    passwordStrengthBar.className = 'password-strength-bar medium';
                    passwordStrengthText.textContent = 'Medium';
                    passwordStrengthText.className = 'password-strength-text medium';
                } else {
                    passwordStrengthBar.className = 'password-strength-bar strong';
                    passwordStrengthText.textContent = 'Strong';
                    passwordStrengthText.className = 'password-strength-text strong';
                }
            }

            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    updatePasswordStrength(this.value);
                    if (confirmPasswordInput && confirmPasswordInput.value) {
                        checkPasswordMatch();
                    }
                });
            }

            function checkPasswordMatch() {
                if (!confirmPasswordInput || !confirmPasswordInput.value) {
                    passwordMatchMsg.textContent = '';
                    return;
                }
                if (passwordInput.value === confirmPasswordInput.value) {
                    passwordMatchMsg.textContent = '✓ Passwords match';
                    passwordMatchMsg.className = 'password-match-msg match';
                } else {
                    passwordMatchMsg.textContent = '✗ Passwords do not match';
                    passwordMatchMsg.className = 'password-match-msg no-match';
                }
            }

            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', checkPasswordMatch);
            }

            {{-- Confirmation Modal Logic --}}
            const reviewBtn = document.getElementById('reviewBtn');
            const confirmCreateModal = document.getElementById('confirmCreateModal');
            const confirmCreateClose = document.getElementById('confirmCreateClose');
            const confirmCreateCancel = document.getElementById('confirmCreateCancel');
            const confirmCreateConfirm = document.getElementById('confirmCreateConfirm');
            const confirmCreateBody = document.getElementById('confirmCreateBody');
            const hiddenSubmitBtn = document.getElementById('hiddenSubmitBtn');
            const createForm = document.getElementById('createAccountForm');

            function getSelectedRole() {
                return document.querySelector('input[name="role"]:checked')?.value || 'Admin';
            }

            function getFieldValue(id) {
                const el = document.getElementById(id);
                if (!el) return '';
                if (el.type === 'checkbox') return el.checked ? el.value : '';
                return el.value || '—';
            }

            function renderConfirmModal() {
                const role = getSelectedRole();
                const isStudent = role === 'Student';
                const isTeacher = role === 'Teacher';

                const rows = [
                    { label: 'First Name', value: getFieldValue('first_name') },
                    { label: 'Last Name', value: getFieldValue('last_name') },
                    { label: 'Middle Name', value: getFieldValue('middle_name') },
                    { label: 'Email', value: getFieldValue('email') },
                    { label: 'Role', value: role },
                    { label: 'Status', value: getFieldValue('status') },
                    { label: 'Password', value: '••••••••' },
                    { label: 'Gender', value: getFieldValue('gender') || '—' },
                    { label: 'Birthdate', value: getFieldValue('birthdate') },
                    { label: 'Contact', value: getFieldValue('contact_number') },
                    { label: 'Address', value: getFieldValue('address') },
                    { label: 'Is Deleted', value: getFieldValue('is_deleted') === '1' ? 'Yes' : 'No' },
                ];

                let studentRows = [];
                let teacherRows = [];

                if (isStudent) {
                    studentRows = [
                        { label: 'LRN', value: getFieldValue('student_lrn') || '—' },
                        { label: 'Student Number', value: getFieldValue('student_number') || '—' },
                        { label: 'Grade Level', value: getFieldValue('grade_level') || '11' },
                        { label: 'Guardian ID', value: getFieldValue('student_guardian_id') || '—' },
                    ];
                } else if (isTeacher) {
                    teacherRows = [
                        { label: 'Teacher Number', value: getFieldValue('teacher_number') || '—' },
                        { label: 'Specialization', value: getFieldValue('specialization') || '—' },
                    ];
                }

                let html = '<div class="confirm-create-modal__row"><span class="confirm-create-modal__label">Basic Info</span><span class="confirm-create-modal__value"></span></div>';
                rows.forEach(row => {
                    html += `
                        <div class="confirm-create-modal__row">
                            <span class="confirm-create-modal__label">${row.label}</span>
                            <span class="confirm-create-modal__value">${row.value}</span>
                        </div>`;
                });

                if (isStudent && studentRows.length > 0) {
                    html += '<div class="confirm-create-modal__section-title">Student Details</div>';
                    studentRows.forEach(row => {
                        html += `
                            <div class="confirm-create-modal__row">
                                <span class="confirm-create-modal__label">${row.label}</span>
                                <span class="confirm-create-modal__value">${row.value}</span>
                            </div>`;
                    });
                }

                if (isTeacher && teacherRows.length > 0) {
                    html += '<div class="confirm-create-modal__section-title">Teacher Details</div>';
                    teacherRows.forEach(row => {
                        html += `
                            <div class="confirm-create-modal__row">
                                <span class="confirm-create-modal__label">${row.label}</span>
                                <span class="confirm-create-modal__value">${row.value}</span>
                            </div>`;
                    });
                }

                // Check password match
                const passwordsMatch = passwordInput.value === confirmPasswordInput.value;
                html += `
                    <div class="confirm-create-modal__row" style="margin-top:0.5rem">
                        <span class="confirm-create-modal__label">Password Match</span>
                        <span class="confirm-create-modal__value" style="color:${passwordsMatch ? '#10b981' : '#ef4444'}">
                            ${passwordsMatch ? '✓ Match' : '✗ No Match'}
                        </span>
                    </div>`;

                confirmCreateBody.innerHTML = html;
            }

            {{-- Validate required fields & toggle Review button --}}
            function validateForm() {
                const firstName = document.getElementById('first_name')?.value.trim();
                const lastName  = document.getElementById('last_name')?.value.trim();
                const email     = document.getElementById('email')?.value.trim();
                const password  = document.getElementById('password')?.value;
                const confirm   = document.getElementById('password_confirmation')?.value;
                const role      = document.querySelector('input[name="role"]:checked');

                const allFilled = firstName && lastName && email && password && confirm && role;
                const pwMatch   = password === confirm;

                if (reviewBtn) {
                    reviewBtn.disabled = !(allFilled && pwMatch);
                }
            }

            // Run on every input/change inside the form
            if (createForm) {
                createForm.addEventListener('input', validateForm);
                createForm.addEventListener('change', validateForm);
            }
            validateForm(); // initial state on page load

            if (reviewBtn) {
                reviewBtn.addEventListener('click', function() {
                    renderConfirmModal();
                    if (confirmCreateModal) confirmCreateModal.showModal();
                });
            }

            if (confirmCreateClose) confirmCreateClose.addEventListener('click', function() {
                if (confirmCreateModal) confirmCreateModal.close();
            });
            if (confirmCreateCancel) confirmCreateCancel.addEventListener('click', function() {
                if (confirmCreateModal) confirmCreateModal.close();
            });
            if (confirmCreateModal) confirmCreateModal.addEventListener('click', function(e) {
                if (e.target === confirmCreateModal) confirmCreateModal.close();
            });

            if (confirmCreateConfirm) confirmCreateConfirm.addEventListener('click', function() {
                if (createForm) {
                    createForm.requestSubmit();
                }
            });
        });
    </script>
@endsection
