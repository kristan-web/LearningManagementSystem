@props(['users', 'showSearchFilter' => true, 'showActionsModal' => true, 'showActions' => true])

<section class="admin-users-panel" data-user-table>
    <div class="admin-users-panel__header">
        <div>
            <h2 class="admin-users-panel__title">Latest User Accounts</h2>
            <p class="admin-users-panel__subtitle">Manage and search user accounts.</p>
        </div>
        <span class="admin-users-panel__count">{{ $users->count() }} accounts</span>
    </div>

    {{-- Search & Filter --}}
    @if($showSearchFilter)
    <form method="GET" action="{{ route('admin.users.index') }}" class="admin-users-panel__filters">
        <div class="admin-users-panel__search">
            <label for="admin-user-search" class="sr-only">Search users</label>
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" id="admin-user-search" name="search" class="admin-users-panel__search-input" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="admin-users-panel__filter-buttons">
            <button type="button" class="admin-users-panel__filter-btn" data-dropdown-toggle="admin-filter-dropdown">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="currentColor" viewbox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                Filter
                <svg class="w-3 h-3 ml-1" fill="currentColor" viewbox="0 0 20 20">
                    <path clip-rule="evenodd" fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                </svg>
            </button>
            <div id="admin-filter-dropdown" class="admin-users-panel__dropdown hidden">
                <h6 class="admin-users-panel__dropdown-title">Filter by Role</h6>
                <ul class="space-y-2 text-sm">
                    @foreach(['Admin', 'Staff', 'Registrar', 'Accounting', 'Teacher', 'Student'] as $roleOption)
                        <li class="flex items-center">
                            <input type="checkbox" name="role" value="{{ $roleOption }}" {{ request('role') === $roleOption ? 'checked' : '' }} onchange="this.form.submit()" class="admin-users-panel__checkbox" data-filter="role" value="{{ $roleOption }}">
                            <label class="ml-2 text-sm font-medium">{{ $roleOption }}</label>
                        </li>
                    @endforeach
                </ul>
                <h6 class="admin-users-panel__dropdown-title mt-3">Filter by Status</h6>
                <ul class="space-y-2 text-sm">
                    <li class="flex items-center">
                        <input type="checkbox" name="deleted" value="0" {{ request('deleted') === '0' ? 'checked' : '' }} onchange="this.form.submit()" class="admin-users-panel__checkbox" data-filter="deleted" value="0">
                        <label class="ml-2 text-sm font-medium">Active</label>
                    </li>
                    <li class="flex items-center">
                        <input type="checkbox" name="deleted" value="1" {{ request('deleted') === '1' ? 'checked' : '' }} onchange="this.form.submit()" class="admin-users-panel__checkbox" data-filter="deleted" value="1">
                        <label class="ml-2 text-sm font-medium">Deleted</label>
                    </li>
                </ul>
                <div class="admin-users-panel__filter-actions mt-3 pt-3 border-t">
                    <button type="button" class="admin-users-panel__clear-btn" id="clearFilters">Clear All</button>
                    <button type="submit" class="admin-users-panel__apply-btn">Apply</button>
                </div>
            </div>
        </div>
    </form>
    @endif

    {{-- Table --}}
    <div class="admin-users-table-wrap">
        <table class="admin-users-table">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col" class="admin-users-table__email">Email</th>
                    <th scope="col" class="admin-users-table__role">Role</th>
                    <th scope="col" class="admin-users-table__status">Status</th>
                    <th scope="col" class="admin-users-table__created">Created</th>
                    @if($showActions)
                    <th scope="col" class="admin-users-table__actions">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr
                        class="admin-users-table__row"
                        tabindex="0"
                        role="button"
                        data-user-row
                        data-user-id="{{ $user->user_id }}"
                        data-user-name="{{ trim($user->first_name . ' ' . $user->last_name) }}"
                        data-user-email="{{ $user->email }}"
                        data-user-role="{{ $user->role }}"
                        data-user-status="{{ $user->status }}"
                        data-user-created="{{ $user->created_at?->format('M d, Y h:i A') }}"
                        data-user-middle-name="{{ $user->middle_name ?: 'Not provided' }}"
                        data-user-contact="{{ $user->contact_number ?: 'Not provided' }}"
                        data-user-address="{{ $user->address ?: 'Not provided' }}"
                        data-user-birthdate="{{ $user->birthdate?->format('M d, Y') ?: 'Not provided' }}"
                        data-user-gender="{{ $user->gender ?: 'Not provided' }}"
                        data-user-is-deleted="{{ $user->is_deleted }}"
                    >
                        <th scope="row">{{ trim($user->first_name . ' ' . $user->last_name) }}</th>
                        <td class="admin-users-table__email">{{ $user->email }}</td>
                        <td class="admin-users-table__role">{{ $user->role }}</td>
                        <td class="admin-users-table__status">{{ $user->status }}</td>
                        <td class="admin-users-table__created">{{ $user->created_at?->format('M d, Y') }}</td>
                        @if($showActions)
                        <td class="admin-users-table__actions" onclick="event.stopPropagation()">
                            <div class="admin-users-table__action-wrap">
                                <a href="{{ route('admin.users.edit', $user->user_id) }}"
                                   class="admin-users-table__action-btn admin-users-table__action-btn--edit"
                                   aria-label="Edit {{ trim($user->first_name . ' ' . $user->last_name) }}"
                                   title="Edit"
                                   onclick="event.stopPropagation()">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                </a>
                                @if($user->is_deleted)
                                <button type="button"
                                        class="admin-users-table__action-btn admin-users-table__action-btn--restore"
                                        aria-label="Restore {{ trim($user->first_name . ' ' . $user->last_name) }}"
                                        title="Undo / Restore"
                                        data-restore-user-id="{{ $user->user_id }}"
                                        data-restore-user-name="{{ trim($user->first_name . ' ' . $user->last_name) }}"
                                        onclick="event.stopPropagation()">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                </button>
                                @else
                                <button type="button"
                                        class="admin-users-table__action-btn admin-users-table__action-btn--delete"
                                         aria-label="Delete {{ trim($user->first_name . ' ' . $user->last_name) }}"
                                         title="Delete"
                                         data-delete-user-id="{{ $user->user_id }}"
                                         data-delete-user-name="{{ trim($user->first_name . ' ' . $user->last_name) }}"
                                         onclick="event.stopPropagation()">
                                     <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                 </button>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showActions ? 6 : 5 }}" class="admin-users-table__empty">No user accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($showSearchFilter)
    @if(method_exists($users, 'hasPages') && $users->hasPages())
        <div class="admin-users-panel__pagination">
            {{ $users->withQueryString()->links() }}
        </div>
    @endif
    @endif
</section>

{{-- Action Modal (pop-up) — always rendered so row-click works on compact screens --}}
<dialog class="admin-user-modal" data-user-modal aria-labelledby="admin-user-modal-title">
    <div class="admin-user-modal__content">
        <div class="admin-user-modal__header">
            <div>
                <p class="admin-user-modal__eyebrow">{{ $showActionsModal ? 'Account Actions' : 'Account Details' }}</p>
                <h2 id="admin-user-modal-title" data-modal-value="name">User Account</h2>
            </div>
            <button type="button" class="admin-user-modal__close" data-modal-close aria-label="Close account details">&times;</button>
        </div>
        <dl class="admin-user-modal__details">
            <div><dt>Email</dt><dd data-modal-value="email"></dd></div>
            <div><dt>Role</dt><dd data-modal-value="role"></dd></div>
            <div><dt>Status</dt><dd data-modal-value="status"></dd></div>
            <div><dt>Created</dt><dd data-modal-value="created"></dd></div>
        </dl>
        @if($showActionsModal)
        <div class="admin-user-modal__actions mt-4 pt-3 border-t">
            <a href="#" id="modal-action-edit" class="admin-user-modal__action-btn admin-user-modal__action-btn--edit">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewbox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                Edit
            </a>
            <button type="button" id="modal-action-delete" class="admin-user-modal__action-btn admin-user-modal__action-btn--delete">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewbox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                Delete
            </button>
            <button type="button" id="modal-action-restore" class="admin-user-modal__action-btn admin-user-modal__action-btn--restore hidden">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewbox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                Restore
            </button>
        </div>
        @endif
    </div>
</dialog>

{{-- Confirmation Modal --}}
<dialog class="admin-confirm-modal" id="confirmModal" aria-labelledby="confirm-modal-title">
    <div class="admin-confirm-modal__content">
        <div class="admin-confirm-modal__header">
            <div class="flex items-center gap-3">
                <div class="admin-confirm-modal__icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div>
                    <h2 id="confirm-modal-title" class="admin-confirm-modal__title">Confirm Action</h2>
                    <p id="confirm-modal-message" class="admin-confirm-modal__message">Are you sure you want to proceed?</p>
                </div>
            </div>
            <button type="button" class="admin-confirm-modal__close" id="confirmModalClose" aria-label="Close">&times;</button>
        </div>
        <div class="admin-confirm-modal__actions">
            <button type="button" class="admin-confirm-modal__btn admin-confirm-modal__btn--cancel" id="confirmModalCancel">Cancel</button>
            <button type="button" class="admin-confirm-modal__btn admin-confirm-modal__btn--confirm" id="confirmModalConfirm">Confirm</button>
        </div>
    </div>
</dialog>

{{-- Delete OTP Modal: code is emailed to the acting admin and required to delete --}}
<dialog class="admin-confirm-modal" id="deleteOtpModal" aria-labelledby="delete-otp-title">
    <form class="admin-confirm-modal__content" id="deleteOtpForm">
        <div class="admin-confirm-modal__header">
            <div class="flex items-center gap-3">
                <div class="admin-confirm-modal__icon admin-otp__icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h2 id="delete-otp-title" class="admin-confirm-modal__title">Enter verification code</h2>
                    <p id="delete-otp-status" class="admin-confirm-modal__message" aria-live="polite"></p>
                </div>
            </div>
            <button type="button" class="admin-confirm-modal__close" data-otp-close aria-label="Close">&times;</button>
        </div>
        <div class="admin-otp__boxes" role="group" aria-label="6-digit code">
            @for ($i = 0; $i < 6; $i++)
                <input type="text" class="admin-otp__digit" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit {{ $i + 1 }}" {{ $i === 0 ? 'autocomplete=one-time-code' : 'autocomplete=off' }} required>
            @endfor
        </div>
        <p id="deleteOtpError" class="admin-otp__error" role="alert"></p>
        <div class="admin-confirm-modal__actions">
            <button type="button" class="admin-confirm-modal__btn admin-confirm-modal__btn--cancel" data-otp-close>Cancel</button>
            <button type="submit" class="admin-confirm-modal__btn admin-confirm-modal__btn--confirm" id="deleteOtpSubmit">Delete</button>
        </div>
    </form>
</dialog>

<style>
    {{-- Theme tokens: light mode (default) uses the light blue palette, html.dark switches to dark --}}
    :root {
        --ut-panel-bg: linear-gradient(180deg, #ffffff 0%, #f5f9ff 100%);
        --ut-panel-border: #dbeafe;
        --ut-panel-shadow: 0 1px 2px rgb(15 23 42 / 0.04), 0 12px 32px -16px rgb(37 99 235 / 0.25);
        --ut-divider: #e0ecff;
        --ut-text: #0f172a;
        --ut-text-soft: #334155;
        --ut-muted: #64748b;
        --ut-accent: #2563eb;
        --ut-accent-hover: #1d4ed8;
        --ut-accent-soft: #eff6ff;
        --ut-accent-ring: rgb(59 130 246 / 0.25);
        --ut-input-bg: #ffffff;
        --ut-input-border: #cbd5e1;
        --ut-row-bg: #ffffff;
        --ut-row-border: #e2e8f0;
        --ut-row-hover: #f0f7ff;
        --ut-row-hover-border: #bfdbfe;
        --ut-chip-bg: #eff6ff;
        --ut-chip-text: #1d4ed8;
        --ut-surface: #ffffff;
        --ut-surface-soft: #f8fafc;
        --ut-cancel-bg: #f1f5f9;
        --ut-cancel-text: #334155;
        --ut-backdrop: rgb(15 23 42 / 0.45);
        --ut-modal-shadow: 0 24px 60px -12px rgb(15 23 42 / 0.3);
    }
    html.dark {
        --ut-panel-bg: linear-gradient(180deg, #1e293b 0%, #172033 100%);
        --ut-panel-border: #334155;
        --ut-panel-shadow: 0 12px 32px -16px rgb(0 0 0 / 0.6);
        --ut-divider: #334155;
        --ut-text: #f8fafc;
        --ut-text-soft: #e2e8f0;
        --ut-muted: #94a3b8;
        --ut-accent: #3b82f6;
        --ut-accent-hover: #60a5fa;
        --ut-accent-soft: rgb(59 130 246 / 0.12);
        --ut-accent-ring: rgb(96 165 250 / 0.3);
        --ut-input-bg: #0f172a;
        --ut-input-border: #475569;
        --ut-row-bg: rgb(15 23 42 / 0.45);
        --ut-row-border: #334155;
        --ut-row-hover: rgb(59 130 246 / 0.1);
        --ut-row-hover-border: rgb(96 165 250 / 0.4);
        --ut-chip-bg: rgb(59 130 246 / 0.15);
        --ut-chip-text: #93c5fd;
        --ut-surface: #1e293b;
        --ut-surface-soft: #0f172a;
        --ut-cancel-bg: #334155;
        --ut-cancel-text: #f1f5f9;
        --ut-backdrop: rgb(2 6 23 / 0.7);
        --ut-modal-shadow: 0 24px 60px -12px rgb(0 0 0 / 0.7);
    }

    .admin-users-panel {
        width: 100%;
        max-width: 72rem;
        margin: 1rem auto 0;
        overflow: visible;
        border: 1px solid var(--ut-panel-border);
        border-radius: 1rem;
        background: var(--ut-panel-bg);
        box-shadow: var(--ut-panel-shadow);
        transition: background .3s ease, border-color .3s ease, box-shadow .3s ease;
    }
    .admin-users-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--ut-divider);
    }
    .admin-users-panel__title { margin: 0; color: var(--ut-text); font-size: 1.125rem; font-weight: 700; letter-spacing: -0.01em; }
    .admin-users-panel__subtitle { margin: 0.25rem 0 0; color: var(--ut-muted); font-size: 0.8125rem; }
    .admin-users-panel__count {
        margin: 0;
        white-space: nowrap;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        background: var(--ut-chip-bg);
        color: var(--ut-chip-text);
        font-size: 0.75rem;
        font-weight: 600;
    }

    {{-- Search & Filter Styles --}}
    .admin-users-panel__filters {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--ut-divider);
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }
    .admin-users-panel__search {
        flex: 1;
        min-width: 200px;
    }
    .admin-users-panel__search svg { color: var(--ut-muted); }
    .admin-users-panel__search-input {
        width: 100%;
        padding: 0.5625rem 0.75rem 0.5625rem 2.5rem;
        border-radius: 0.625rem;
        border: 1px solid var(--ut-input-border);
        background: var(--ut-input-bg);
        color: var(--ut-text);
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .admin-users-panel__search-input:focus {
        border-color: var(--ut-accent);
        box-shadow: 0 0 0 3px var(--ut-accent-ring);
    }
    .admin-users-panel__search-input::placeholder {
        color: var(--ut-muted);
    }
    .admin-users-panel__filter-buttons {
        position: relative;
    }
    .admin-users-panel__filter-btn {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.5625rem 0.875rem;
        border-radius: 0.625rem;
        border: 1px solid var(--ut-input-border);
        background: var(--ut-input-bg);
        color: var(--ut-text-soft);
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, color 0.2s;
    }
    .admin-users-panel__filter-btn:hover {
        background: var(--ut-accent-soft);
        border-color: var(--ut-row-hover-border);
        color: var(--ut-accent);
    }
    .admin-users-panel__dropdown {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        z-index: 50;
        min-width: 220px;
        padding: 1rem;
        background: var(--ut-surface);
        border: 1px solid var(--ut-panel-border);
        border-radius: 0.875rem;
        box-shadow: var(--ut-modal-shadow);
    }
    .admin-users-panel__dropdown.hidden { display: none; }
    .admin-users-panel__dropdown-title {
        color: var(--ut-muted);
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
        margin-top: 0.75rem;
    }
    .admin-users-panel__dropdown-title:first-child { margin-top: 0; }
    .admin-users-panel__dropdown-title.mt-3 { margin-top: 1rem; }
    .admin-users-panel__checkbox {
        width: 1rem;
        height: 1rem;
        accent-color: var(--ut-accent);
        cursor: pointer;
    }
    .admin-users-panel__dropdown label {
        color: var(--ut-text-soft);
        font-size: 0.8125rem;
    }
    .admin-users-panel__filter-actions {
        display: flex;
        gap: 0.5rem;
        border-color: var(--ut-divider);
    }
    .admin-users-panel__clear-btn,
    .admin-users-panel__apply-btn {
        flex: 1;
        padding: 0.4375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: background 0.2s, color 0.2s;
    }
    .admin-users-panel__clear-btn {
        background: transparent;
        color: var(--ut-muted);
        border: 1px solid var(--ut-input-border);
    }
    .admin-users-panel__clear-btn:hover {
        background: var(--ut-cancel-bg);
        color: var(--ut-text);
    }
    .admin-users-panel__apply-btn {
        background: var(--ut-accent);
        color: #fff;
    }
    .admin-users-panel__apply-btn:hover {
        background: var(--ut-accent-hover);
    }

    .admin-users-table-wrap { width: 100%; padding-bottom: 0.5rem; }
    .admin-users-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 0 0.5rem;
        padding: 0 1rem;
        text-align: left;
        color: var(--ut-muted);
        font-size: 0.875rem;
    }
    .admin-users-table th,
    .admin-users-table td { padding: 0.875rem 1rem; vertical-align: middle; }
    .admin-users-table thead th {
        padding-top: 0.75rem;
        padding-bottom: 0.25rem;
        color: var(--ut-muted);
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }
    .admin-users-table__row { cursor: pointer; outline: none; }
    .admin-users-table__row th,
    .admin-users-table__row td {
        background: var(--ut-row-bg);
        border-top: 1px solid var(--ut-row-border);
        border-bottom: 1px solid var(--ut-row-border);
        overflow-wrap: anywhere;
        transition: background 0.2s, border-color 0.2s;
    }
    .admin-users-table__row th { border-left: 1px solid var(--ut-row-border); border-radius: 0.75rem 0 0 0.75rem; color: var(--ut-text); font-weight: 600; }
    .admin-users-table__row td:last-child { border-right: 1px solid var(--ut-row-border); border-radius: 0 0.75rem 0.75rem 0; }
    .admin-users-table__row:hover th,
    .admin-users-table__row:hover td,
    .admin-users-table__row:focus-visible th,
    .admin-users-table__row:focus-visible td {
        background: var(--ut-row-hover);
        border-color: var(--ut-row-hover-border);
    }
    .admin-users-table__row td.admin-users-table__role { color: var(--ut-accent); font-weight: 600; }
    .admin-users-table__row td.admin-users-table__status { color: var(--ut-text-soft); font-weight: 500; }
    .admin-users-table__empty { padding: 2.5rem 1rem; text-align: center; color: var(--ut-muted); }

    .admin-users-panel__pagination {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--ut-divider);
    }
    .admin-users-panel__pagination .pagination {
        display: flex;
        justify-content: center;
        gap: 0.25rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .admin-users-panel__pagination .pagination li a,
    .admin-users-panel__pagination .pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: var(--ut-text-soft);
        text-decoration: none;
        border: 1px solid var(--ut-row-border);
        background: var(--ut-row-bg);
        transition: background 0.2s, color 0.2s, border-color 0.2s;
    }
    .admin-users-panel__pagination .pagination li a:hover {
        background: var(--ut-accent-soft);
        border-color: var(--ut-row-hover-border);
        color: var(--ut-accent);
    }
    .admin-users-panel__pagination .pagination li[aria-current="page"] span {
        background: var(--ut-accent);
        color: #fff;
        border-color: var(--ut-accent);
    }

    {{-- Modal Styles --}}
    .admin-user-modal {
        position: fixed;
        inset: 0;
        margin: auto;
        width: min(32rem, calc(100% - 2rem));
        max-height: calc(100vh - 2rem);
        padding: 0;
        border: 1px solid var(--ut-panel-border);
        border-radius: 1rem;
        color: var(--ut-text);
        background: var(--ut-surface);
        box-shadow: var(--ut-modal-shadow);
    }
    .admin-user-modal::backdrop { background: var(--ut-backdrop); backdrop-filter: blur(4px); }
    .admin-user-modal__content { padding: 1.5rem; }
    .admin-user-modal__header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--ut-divider); }
    .admin-user-modal__eyebrow { margin: 0 0 0.25rem; color: var(--ut-accent); font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
    .admin-user-modal h2 { margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--ut-text); }
    .admin-user-modal__close,
    .admin-confirm-modal__close {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border: 0;
        border-radius: 9999px;
        color: var(--ut-muted);
        background: transparent;
        cursor: pointer;
        font-size: 1.5rem;
        line-height: 1;
        transition: background 0.2s, color 0.2s;
    }
    .admin-user-modal__close:hover,
    .admin-confirm-modal__close:hover { background: var(--ut-cancel-bg); color: var(--ut-text); }
    .admin-user-modal__details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; margin: 1.25rem 0 0; }
    .admin-user-modal__details div {
        min-width: 0;
        padding: 0.75rem;
        border-radius: 0.625rem;
        background: var(--ut-surface-soft);
        border: 1px solid var(--ut-row-border);
    }
    .admin-user-modal__details dt { color: var(--ut-muted); font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
    .admin-user-modal__details dd { margin: 0.25rem 0 0; overflow-wrap: anywhere; font-size: 0.875rem; font-weight: 500; color: var(--ut-text); }
    .admin-user-modal__actions { display: flex; flex-direction: column; gap: 0.5rem; border-color: var(--ut-divider); }
    .admin-user-modal__action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.625rem 1rem;
        border-radius: 0.625rem;
        border: none;
        color: #fff;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: filter 0.2s, transform 0.2s;
    }
    .admin-user-modal__action-btn:hover { filter: brightness(1.08); transform: translateY(-1px); }
    .admin-user-modal__action-btn.hidden { display: none; }
    .admin-user-modal__action-btn--show { background: #10b981; }
    .admin-user-modal__action-btn--edit { background: var(--ut-accent); }
    .admin-user-modal__action-btn--delete { background: #ef4444; }

    @media (max-width: 639px) {
        .admin-users-panel__header { align-items: flex-start; flex-direction: column; }
        .admin-users-table { padding: 0 0.5rem; }
        .admin-users-table th, .admin-users-table td { padding: 0.75rem 0.625rem; }
        .admin-users-panel__filters { flex-direction: column; }
        .admin-users-panel__search { width: 100%; }
        .admin-user-modal__details { grid-template-columns: 1fr; }
        .admin-user-modal__actions { flex-direction: column; }
    }
    {{-- Inline action buttons --}}
    .admin-users-table__actions { width: 5rem; text-align: center; }
    .admin-users-table__action-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
    }
    .admin-users-table__action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        transition: background 0.15s, color 0.15s, transform 0.15s;
        text-decoration: none;
        flex-shrink: 0;
    }
    .admin-users-table__action-btn:hover { transform: scale(1.08); }
    .admin-users-table__action-btn--edit  { background: rgb(59 130 246 / 0.12); color: #2563eb; }
    .admin-users-table__action-btn--edit:hover  { background: #3b82f6; color: #fff; }
    .admin-users-table__action-btn--delete { background: rgb(239 68 68 / 0.12); color: #dc2626; }
    .admin-users-table__action-btn--delete:hover { background: #ef4444; color: #fff; }
    .admin-users-table__action-btn--restore { background: rgb(16 185 129 / 0.12); color: #059669; }
    .admin-users-table__action-btn--restore:hover { background: #10b981; color: #fff; }
    html.dark .admin-users-table__action-btn--edit { color: #93c5fd; }
    html.dark .admin-users-table__action-btn--delete { color: #fca5a5; }
    html.dark .admin-users-table__action-btn--restore { color: #6ee7b7; }
    html.dark .admin-users-table__action-btn:hover { color: #fff; }
    .admin-user-modal__action-btn--restore { background: #10b981; }

    {{-- Actions column always hides on compact so icon buttons never conflict with the row-click modal --}}
    @media (max-width: 1279px) { .admin-users-table__actions { display: none !important; } }
    @media (max-width: 767px) { .admin-users-table__role { display: none; } }
    @media (max-width: 1023px) { .admin-users-table__status { display: none; } }
    @media (max-width: 1279px) { .admin-users-table__created { display: none; } }
    @media (min-width: 1280px) {
        .admin-user-modal { display: none; }
        .admin-users-table__row { cursor: default; }
    }
    {{-- Confirmation Modal Styles --}}
    .admin-confirm-modal {
        position: fixed;
        inset: 0;
        margin: auto;
        width: min(28rem, calc(100% - 2rem));
        max-height: calc(100vh - 2rem);
        padding: 0;
        border: 1px solid var(--ut-panel-border);
        border-radius: 1rem;
        background: var(--ut-surface);
        box-shadow: var(--ut-modal-shadow);
    }
    .admin-confirm-modal::backdrop { background: var(--ut-backdrop); backdrop-filter: blur(4px); }
    .admin-confirm-modal__content { padding: 1.5rem; }
    .admin-confirm-modal__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--ut-divider);
    }
    .admin-confirm-modal__icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 9999px;
        color: #f59e0b;
        background: rgb(245 158 11 / 0.12);
    }
    .admin-confirm-modal__title { margin: 0; font-size: 1.125rem; font-weight: 700; color: var(--ut-text); }
    .admin-confirm-modal__message { margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--ut-muted); }
    .admin-confirm-modal__actions {
        display: flex; gap: 0.75rem; justify-content: flex-end;
        padding-top: 1rem;
    }
    .admin-confirm-modal__btn {
        padding: 0.5rem 1.125rem; border-radius: 0.625rem;
        font-size: 0.875rem; font-weight: 600; cursor: pointer; border: none;
        transition: filter 0.2s, background 0.2s;
    }
    .admin-confirm-modal__btn:hover { filter: brightness(1.08); }
    .admin-confirm-modal__btn--cancel {
        background: var(--ut-cancel-bg); color: var(--ut-cancel-text);
    }
    .admin-confirm-modal__btn--confirm {
        background: #ef4444; color: #fff;
    }
    .admin-otp__icon { color: #ef4444; background: rgb(239 68 68 / 0.12); }
    .admin-otp__boxes { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; }
    .admin-otp__digit {
        width: 3rem; height: 3.5rem; padding: 0;
        border: 1.5px solid var(--ut-input-border); border-radius: 0.75rem;
        background: var(--ut-input-bg); color: var(--ut-text);
        font-size: 1.5rem; font-weight: 700; text-align: center;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .admin-otp__digit:focus { outline: none; border-color: var(--ut-accent); box-shadow: 0 0 0 4px var(--ut-accent-ring); transform: translateY(-1px); }
    .admin-otp__digit.is-filled { border-color: var(--ut-accent); background: var(--ut-accent-soft); }
    .admin-otp__boxes.is-error .admin-otp__digit { border-color: #ef4444; background: rgb(239 68 68 / 0.08); animation: admin-otp-shake .4s ease; }
    @keyframes admin-otp-shake { 20%, 60% { transform: translateX(-4px); } 40%, 80% { transform: translateX(4px); } }
    @media (max-width: 400px) { .admin-otp__boxes { gap: 0.375rem; } .admin-otp__digit { width: 2.5rem; height: 3rem; font-size: 1.25rem; } }
    .admin-otp__error { min-height: 1.25rem; margin: 0.75rem 0 0; font-size: 0.8125rem; color: #ef4444; text-align: center; }
    .admin-confirm-modal__btn:disabled { opacity: 0.6; cursor: wait; }
</style>

<script>
    (() => {
        const table = document.querySelector('[data-user-table]');
        const modal = document.querySelector('[data-user-modal]');
        if (!table || !modal) return;
        const compactTable = window.matchMedia('(max-width: 1279px)');

        // Get current user data from clicked row
        let currentRow = null;

        const updateRowInteraction = () => {
            table.querySelectorAll('[data-user-row]').forEach((row) => {
                row.tabIndex = compactTable.matches ? 0 : -1;
                row.setAttribute('aria-disabled', compactTable.matches ? 'false' : 'true');
            });
            if (!compactTable.matches && modal.open) modal.close();
        };

        const openModal = (row) => {
            currentRow = row;
            modal.querySelectorAll('[data-modal-value]').forEach((element) => {
                const key = element.dataset.modalValue;
                const dataKey = `user${key.split('-').map((part) => part.charAt(0).toUpperCase() + part.slice(1)).join('')}`;
                element.textContent = row.dataset[dataKey] || 'Not provided';
            });

            // Update action links with user ID
            const userId = row.dataset.userId;
            const isDeleted = row.dataset.userIsDeleted === '1' || row.dataset.userIsDeleted === 'true';
            const showLink = document.getElementById('modal-action-show');
            const editLink = document.getElementById('modal-action-edit');
            const modalDeleteBtn = document.getElementById('modal-action-delete');
            const modalRestoreBtn = document.getElementById('modal-action-restore');

            if (showLink) showLink.href = `{{ url('/admin/users/show') }}/${userId}`;
            if (editLink) editLink.href = `{{ route('admin.users.edit', ':id') }}`.replace(':id', userId);

            if (modalDeleteBtn && modalRestoreBtn) {
                if (isDeleted) {
                    modalDeleteBtn.classList.add('hidden');
                    modalRestoreBtn.classList.remove('hidden');
                } else {
                    modalDeleteBtn.classList.remove('hidden');
                    modalRestoreBtn.classList.add('hidden');
                }
            }

            modal.showModal();
        };

        updateRowInteraction();
        compactTable.addEventListener('change', updateRowInteraction);

        table.querySelectorAll('[data-user-row]').forEach((row) => {
            row.addEventListener('click', () => openModal(row));
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openModal(row);
                }
            });
        });

        modal.querySelector('[data-modal-close]').addEventListener('click', () => modal.close());
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });

        {{-- Inline action buttons: Edit stops propagation, Delete & Restore open confirm modal --}}
        table.querySelectorAll('.admin-users-table__action-btn--delete').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const userName = btn.dataset.deleteUserName || 'this account';
                const userId   = btn.dataset.deleteUserId;
                if (window.openConfirmModal) {
                    window.openConfirmModal(
                        'Delete Account?',
                        `Are you sure you want to delete "${userName}"?`,
                        () => openDeleteOtp(userId)
                    );
                }
            });
        });
        table.querySelectorAll('.admin-users-table__action-btn--restore').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const userName = btn.dataset.restoreUserName || 'this account';
                const userId   = btn.dataset.restoreUserId;
                if (window.openConfirmModal) {
                    window.openConfirmModal(
                        'Restore Account?',
                        `Are you sure you want to restore "${userName}"?`,
                        () => { window.location.href = `/admin/users/restore/${userId}`; }
                    );
                }
            });
        });
        table.querySelectorAll('.admin-users-table__action-btn--edit').forEach((btn) => {
            btn.addEventListener('click', (e) => e.stopPropagation());
        });

        {{-- Clear Filters --}}
        const clearBtn = document.getElementById('clearFilters');
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                window.location.href = '{{ route('admin.users.index') }}';
            });
        }

        {{-- Search on Enter --}}
        const searchInput = document.getElementById('admin-user-search');
        if (searchInput) {
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const form = searchInput.closest('form');
                    if (form) form.requestSubmit();
                }
            });
        }

        {{-- Delete OTP: request a code, then submit it to delete --}}
        const otpModal = document.getElementById('deleteOtpModal');
        const otpForm = document.getElementById('deleteOtpForm');
        const otpBoxes = otpForm.querySelector('.admin-otp__boxes');
        const otpDigits = [...otpForm.querySelectorAll('.admin-otp__digit')];
        const otpValue = () => otpDigits.map((d) => d.value).join('');
        const otpSetError = (msg) => {
            otpError.textContent = msg;
            otpBoxes.classList.remove('is-error');
            if (msg) { void otpBoxes.offsetWidth; otpBoxes.classList.add('is-error'); }
        };
        const otpFill = (value) => {
            otpDigits.forEach((d, i) => { d.value = value[i] || ''; d.classList.toggle('is-filled', !!d.value); });
        };
        otpDigits.forEach((box, i) => {
            box.addEventListener('focus', () => box.select());
            box.addEventListener('input', () => {
                box.value = box.value.replace(/\D/g, '').slice(-1);
                box.classList.toggle('is-filled', !!box.value);
                otpSetError('');
                if (box.value && i < 5) otpDigits[i + 1].focus();
                if (otpValue().length === 6 && !otpSubmit.disabled) otpForm.requestSubmit();
            });
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !box.value && i > 0) otpDigits[i - 1].focus();
                if (e.key === 'ArrowLeft' && i > 0) otpDigits[i - 1].focus();
                if (e.key === 'ArrowRight' && i < 5) otpDigits[i + 1].focus();
            });
            box.addEventListener('paste', (e) => {
                const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                if (!pasted) return;
                e.preventDefault();
                otpFill(pasted);
                otpDigits[Math.min(pasted.length, 5)].focus();
                if (pasted.length === 6 && !otpSubmit.disabled) otpForm.requestSubmit();
            });
        });
        const otpStatus = document.getElementById('delete-otp-status');
        const otpError = document.getElementById('deleteOtpError');
        const otpSubmit = document.getElementById('deleteOtpSubmit');
        let otpUserId = null;

        const postJson = async (url, body = {}) => {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(body),
            });
            const data = await res.json().catch(() => ({}));
            return { ok: res.ok, message: data.message || (res.status === 429 ? 'Too many requests. Please wait a minute.' : 'Something went wrong.') };
        };

        async function openDeleteOtp(userId) {
            otpUserId = userId;
            otpFill('');
            otpSetError('');
            otpStatus.textContent = 'Sending a code to your email...';
            otpSubmit.disabled = true;
            otpModal.showModal();
            const res = await postJson(`/admin/users/delete/${userId}/otp`);
            if (otpUserId !== userId) return; // modal was reopened for another user meanwhile
            otpStatus.textContent = res.ok ? res.message : '';
            otpSetError(res.ok ? '' : res.message);
            otpSubmit.disabled = !res.ok;
            if (res.ok) otpDigits[0].focus();
        }

        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (otpValue().length !== 6) return otpSetError('Enter all 6 digits.');
            otpSubmit.disabled = true;
            const res = await postJson(`/admin/users/delete/${otpUserId}`, { code: otpValue() });
            if (res.ok) return window.location.reload();
            otpSetError(res.message);
            otpFill('');
            otpDigits[0].focus();
            otpSubmit.disabled = false;
        });
        otpModal.querySelectorAll('[data-otp-close]').forEach((btn) => btn.addEventListener('click', () => otpModal.close()));

        {{-- Confirmation Modal Logic --}}
        const confirmModal = document.getElementById('confirmModal');
        const confirmModalClose = document.getElementById('confirmModalClose');
        const confirmModalCancel = document.getElementById('confirmModalCancel');
        const confirmModalConfirm = document.getElementById('confirmModalConfirm');
        const confirmModalMessage = document.getElementById('confirm-modal-message');
        const confirmModalTitle = document.getElementById('confirm-modal-title');
        let confirmCallback = null;

        if (confirmModal) {
            function openConfirmModal(title, message, onConfirm) {
                confirmModalTitle.textContent = title;
                confirmModalMessage.textContent = message;
                confirmCallback = onConfirm;
                confirmModal.showModal();
            }

            function closeConfirmModal() {
                confirmModal.close();
                confirmCallback = null;
            }

            if (confirmModalClose) confirmModalClose.addEventListener('click', closeConfirmModal);
            if (confirmModalCancel) confirmModalCancel.addEventListener('click', closeConfirmModal);
            if (confirmModal) confirmModal.addEventListener('click', (e) => {
                if (e.target === confirmModal) closeConfirmModal();
            });
            if (confirmModalConfirm) confirmModalConfirm.addEventListener('click', () => {
                if (confirmCallback) confirmCallback();
                closeConfirmModal();
            });

            window.openConfirmModal = openConfirmModal;
        }

        {{-- Delete button triggers confirmation --}}
        const deleteBtn = document.getElementById('modal-action-delete');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                if (currentRow && window.openConfirmModal) {
                    const userName = currentRow.dataset.userName || 'this account';
                    window.openConfirmModal(
                        'Delete Account?',
                        `Are you sure you want to delete ${userName}?`,
                        () => openDeleteOtp(currentRow.dataset.userId)
                    );
                }
            });
        }

        {{-- Restore button triggers confirmation --}}
        const restoreBtn = document.getElementById('modal-action-restore');
        if (restoreBtn) {
            restoreBtn.addEventListener('click', () => {
                if (currentRow && window.openConfirmModal) {
                    const userName = currentRow.dataset.userName || 'this account';
                    window.openConfirmModal(
                        'Restore Account?',
                        `Are you sure you want to restore ${userName}?`,
                        () => {
                            window.location.href = `/admin/users/restore/${currentRow.dataset.userId}`;
                        }
                    );
                }
            });
        }
    })();
</script>
