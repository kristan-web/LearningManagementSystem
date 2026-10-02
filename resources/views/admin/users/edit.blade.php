{{-- Admin: Edit User --}}
@extends('layouts.admin')
@section('title', 'Edit Account')

@section('styles')
    <style>
        .create-form-container {
            max-width: 72rem;
            margin: 0 auto;
        }
        .create-form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .create-form-grid .full-width {
            grid-column: span 2;
        }
        @media (max-width: 640px) {
            .create-form-grid {
                grid-template-columns: 1fr;
            }
            .create-form-grid .full-width {
                grid-column: span 1;
            }
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #455072;
            margin-bottom: 0.375rem;
        }
        .form-group label.required::after {
            content: ' *';
            color: #ef4444;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #16244f;
            background: #fff;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2f5fd0;
            ring: 2px solid #2f5fd0;
        }
        .form-group input::placeholder,
        .form-group select::placeholder {
            color: #9ca3af;
        }
        .dark .form-group input,
        .dark .form-group select {
            background: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }
        .dark .form-group input:focus,
        .dark .form-group select:focus {
            border-color: #2f5fd0;
        }
        .dark .form-section {
            background: #1f2937;
            border-color: #374151;
        }
        .dark .form-section h3 {
            color: #fff;
        }
        .dark .btn-cancel {
            background: #374151;
            color: #fff;
            border-color: #4b5563;
        }
        .dark .btn-cancel:hover {
            background: #4b5563;
        }
        .dark .form-group .error-text {
            color: #f87171;
        }
        .form-section {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .form-section h3 {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-weight: 600;
            color: #16244f;
        }
        .btn-cancel {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            background: #fff;
            color: #455072;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-cancel:hover {
            background: #f3f4f6;
        }

        {{-- Password Strength & Toggle --}}
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 3rem;
        }
        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 0.25rem;
            display: flex;
        }
        .password-toggle:hover {
            color: #2f5fd0;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            margin-top: 0.375rem;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.3s, background-color 0.3s;
        }
        .password-strength-bar.weak { width: 33%; background-color: #ef4444; }
        .password-strength-bar.medium { width: 66%; background-color: #f59e0b; }
        .password-strength-bar.strong { width: 100%; background-color: #10b981; }
        .password-strength-text {
            font-size: 0.7rem;
            margin-top: 0.125rem;
        }
        .password-strength-text.weak { color: #ef4444; }
        .password-strength-text.medium { color: #f59e0b; }
        .password-strength-text.strong { color: #10b981; }
        .confirm-password-wrapper {
            position: relative;
        }
        .confirm-password-wrapper input {
            padding-right: 3rem;
        }
        .confirm-password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            padding: 0.25rem;
            display: flex;
        }
        .confirm-password-toggle:hover {
            color: #2f5fd0;
        }
        .password-match-msg {
            font-size: 0.75rem;
            margin-top: 0.125rem;
        }
        .password-match-msg.match { color: #10b981; }
        .password-match-msg.no-match { color: #ef4444; }

        {{-- Confirmation Modal --}}
        .confirm-create-modal {
            position: fixed;
            inset: 0;
            margin: auto;
            width: min(36rem, calc(100% - 2rem));
            max-height: calc(100vh - 2rem);
            padding: 0;
            border: 0;
            border-radius: 0.75rem;
            background: #1f2937;
            box-shadow: 0 24px 60px rgb(0 0 0 / 0.25);
        }
        .confirm-create-modal::backdrop { background: rgb(0 0 0 / 0.55); }
        .confirm-create-modal__content { padding: 1.5rem; }
        .confirm-create-modal__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1rem;
        }
        .confirm-create-modal__title { margin: 0; font-size: 1.25rem; color: #fff; }
        .confirm-create-modal__close {
            border: 0; color: #6b7280; background: transparent;
            cursor: pointer; font-size: 1.75rem; line-height: 1;
        }
        .confirm-create-modal__body {
            max-height: 50vh;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .confirm-create-modal__body::-webkit-scrollbar { display: none; }
        .confirm-create-modal__row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #374151;
        }
        .confirm-create-modal__label {
            color: #9ca3af;
            font-size: 0.8125rem;
            font-weight: 500;
        }
        .confirm-create-modal__value {
            color: #f9fafb;
            font-size: 0.875rem;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }
        .confirm-create-modal__section-title {
            color: #8fb0f5;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 1rem;
            margin-bottom: 0.25rem;
        }
        .confirm-create-modal__actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            padding-top: 1rem;
            margin-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        .role-selector {
            display: flex;
            gap: 0.75rem;
        }
        .role-option {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border: 2px solid #d1d5db;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }
        .role-option:hover {
            border-color: #2f5fd0;
        }
        .role-option.selected {
            border-color: #2f5fd0;
            background: #f0f4fd;
        }
        .role-option input[type="radio"] {
            display: none;
        }
        .role-option label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            display: block;
            color: #455072;
        }
        .role-option.selected label {
            color: #16244f;
        }
        #student-fields, #teacher-fields {
            display: none;
        }
        #student-fields.active, #teacher-fields.active {
            display: block;
        }
        .chart-center {
            padding-top: 2rem;
            padding-bottom: 1rem;
            text-align: start;
            max-width: 72rem;
            margin: 0 auto 1rem auto;
            width: 100%;
        }
    </style>
@endsection

@section('content')
    <div class="create-form-container">
        <div class="flex items-center justify-between mb-4">
            <div class="chart-center">
                <h1 class="text-2xl font-bold text-ink dark:text-white">Edit Account</h1>
                <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Update account details for {{ trim($user->first_name . ' ' . $user->last_name) }}.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user->user_id) }}" id="editAccountForm">
            @csrf
            @method('PUT')

            {{-- Basic Account Info --}}
            <div class="form-section">
                <h3>Account Information</h3>
                <div class="create-form-grid">
                    <div class="form-group">
                        <label class="required" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                        @error('last_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                        @error('middle_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="password">Password (Leave blank to keep current)</label>
                        <div class="password-wrapper">
                            <input type="password" autocomplete="new-password" id="password" name="password" minlength="8" placeholder="Enter new password (optional)">
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
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="confirm-password-wrapper">
                            <input type="password" autocomplete="new-password" id="password_confirmation" name="password_confirmation" minlength="8" placeholder="Confirm new password">
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
                            <div class="role-option {{ old('role', $user->role) === 'Student' ? 'selected' : '' }}" id="role-option-student">
                                <input type="radio" id="role_student" name="role" value="Student" {{ old('role', $user->role) === 'Student' ? 'checked' : '' }}>
                                <label for="role_student">Student</label>
                            </div>
                            <div class="role-option {{ old('role', $user->role) === 'Teacher' ? 'selected' : '' }}" id="role-option-teacher">
                                <input type="radio" id="role_teacher" name="role" value="Teacher" {{ old('role', $user->role) === 'Teacher' ? 'checked' : '' }}>
                                <label for="role_teacher">Teacher</label>
                            </div>
                            @php
                                $adminRoleValue = in_array(old('role', $user->role), ['Admin','Staff','Registrar','Accounting']) ? old('role', $user->role) : 'Admin';
                            @endphp
                            <div class="role-option {{ in_array(old('role', $user->role), ['Admin','Staff','Registrar','Accounting']) ? 'selected' : '' }}" id="role-option-admin">
                                <input type="radio" id="role_admin" name="role" value="{{ $adminRoleValue }}" {{ in_array(old('role', $user->role), ['Admin','Staff','Registrar','Accounting']) ? 'checked' : '' }}>
                                <label for="role_admin">{{ $adminRoleValue }}</label>
                            </div>
                        </div>
                        @error('role')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="required" for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Active" {{ old('status', $user->status) === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $user->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Suspended" {{ old('status', $user->status) === 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="Locked" {{ old('status', $user->status) === 'Locked' ? 'selected' : '' }}>Locked</option>
                        </select>
                        @error('status')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <input type="hidden" id="is_deleted" name="is_deleted" value="{{ old('is_deleted', $user->is_deleted ? '1' : '0') }}">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">-- Select --</option>
                            <option value="Male" {{ old('gender', $user->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $user->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $user->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Birthdate</label>
                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}">
                        @error('birthdate')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" maxlength="20">
                        @error('contact_number')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group full-width">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}">
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
                        <input type="text" id="student_lrn" name="student_lrn" value="{{ old('student_lrn', $student?->lrn) }}" maxlength="12">
                        @error('student_lrn')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="student_number">Student Number</label>
                        <input type="text" id="student_number" name="student_number" value="{{ old('student_number', $student?->student_number) }}" maxlength="20">
                        @error('student_number')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="grade_level">Grade Level</label>
                        <select id="grade_level" name="grade_level">
                            <option value="11" {{ old('grade_level', $student?->grade_level) === '11' ? 'selected' : '' }}>Grade 11</option>
                            <option value="12" {{ old('grade_level', $student?->grade_level) === '12' ? 'selected' : '' }}>Grade 12</option>
                        </select>
                        @error('grade_level')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="student_guardian_id">Guardian ID</label>
                        <input type="number" id="student_guardian_id" name="student_guardian_id" value="{{ old('student_guardian_id', $student?->guardian_id) }}">
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
                        <input type="text" id="teacher_number" name="teacher_number" value="{{ old('teacher_number', $teacher?->teacher_number) }}" maxlength="20">
                        @error('teacher_number')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" value="{{ old('specialization', $teacher?->specialization) }}" placeholder="e.g., Mathematics, Science, English, etc.">
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
                    <svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    Review & Update Account
                </button>
            </div>
        </form>

        {{-- Confirmation Modal --}}
        <dialog class="confirm-create-modal" id="confirmCreateModal">
            <div class="confirm-create-modal__content">
                <div class="confirm-create-modal__header">
                    <h2 class="confirm-create-modal__title">Review Updated Details</h2>
                    <button type="button" class="confirm-create-modal__close" id="confirmCreateClose">&times;</button>
                </div>
                <div class="confirm-create-modal__body" id="confirmCreateBody">
                    {{-- Populated by JS --}}
                </div>
                <div class="confirm-create-modal__actions">
                    <button type="button" class="btn-cancel" id="confirmCreateCancel">Back to Edit</button>
                    <button type="button" class="btn-navy" id="confirmCreateConfirm">
                        <svg class="w-4 h-4" fill="currentColor" viewbox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        Confirm & Update
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
                if (selectedRole === 'Student') document.getElementById('role-option-student')?.classList.add('selected');
                if (selectedRole === 'Teacher') document.getElementById('role-option-teacher')?.classList.add('selected');
                if (['Admin','Staff','Registrar','Accounting'].includes(selectedRole)) {
                    document.getElementById('role-option-admin')?.classList.add('selected');
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
            const editForm = document.getElementById('editAccountForm');

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
                const hasNewPassword = passwordInput && passwordInput.value.length > 0;

                const rows = [
                    { label: 'First Name', value: getFieldValue('first_name') },
                    { label: 'Last Name', value: getFieldValue('last_name') },
                    { label: 'Middle Name', value: getFieldValue('middle_name') },
                    { label: 'Email', value: getFieldValue('email') },
                    { label: 'Role', value: role },
                    { label: 'Status', value: getFieldValue('status') },
                    { label: 'Password', value: hasNewPassword ? '•••••••• (Updated)' : 'Unchanged' },
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

                if (hasNewPassword) {
                    const passwordsMatch = passwordInput.value === confirmPasswordInput.value;
                    html += `
                        <div class="confirm-create-modal__row" style="margin-top:0.5rem">
                            <span class="confirm-create-modal__label">Password Match</span>
                            <span class="confirm-create-modal__value" style="color:${passwordsMatch ? '#10b981' : '#ef4444'}">
                                ${passwordsMatch ? '✓ Match' : '✗ No Match'}
                            </span>
                        </div>`;
                }

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

                const requiredFilled = firstName && lastName && email && role;

                let pwValid = true;
                if (password && password.length > 0) {
                    pwValid = (password.length >= 8) && (password === confirm);
                }

                if (reviewBtn) {
                    reviewBtn.disabled = !(requiredFilled && pwValid);
                }
            }

            // Run on every input/change inside the form
            if (editForm) {
                editForm.addEventListener('input', validateForm);
                editForm.addEventListener('change', validateForm);
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
                if (editForm) {
                    editForm.requestSubmit();
                }
            });
        });
    </script>
@endsection
