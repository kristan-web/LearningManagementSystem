{{-- Partial: Admin Top Navbar (notifications bell + profile shortcut to settings) --}}
@php
    $navUser = auth()->user();
    $navName = trim(($navUser->first_name ?? '') . ' ' . ($navUser->last_name ?? '')) ?: 'Administrator';
    $navInitials = mb_strtoupper(mb_substr($navUser->first_name ?? 'A', 0, 1) . mb_substr($navUser->last_name ?? '', 0, 1));

    $notifications = \App\Models\Notification::with('user')->latest('created_at')->get();
    $unreadCount = $notifications->where('is_read', false)->count();

    $iconBtn = 'relative flex h-10 w-10 items-center justify-center rounded-full border border-ink/10 bg-white text-ink/70 shadow-sm transition hover:text-brand hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white';
@endphp

<nav class="sticky top-0 z-20 -mx-4 -mt-4 mb-4 border-b border-ink/10 bg-white/80 py-3 pl-16 pr-4 backdrop-blur dark:border-slate-700 dark:bg-slate-900/80"
     x-data="{
        open: false,
        unread: {{ $unreadCount }},
        items: @js($notifications->map(fn ($n) => [
            'id' => $n->notification_id,
            'type' => $n->type,
            'message' => $n->message,
            'is_read' => $n->is_read,
            'user' => $n->user ? trim($n->user->first_name . ' ' . $n->user->last_name) : 'Unknown user',
            'time' => $n->created_at?->diffForHumans(),
        ])->values()),
        markRead(item) {
            if (item.is_read) return;
            axios.post(`{{ url('/admin/notifications') }}/${item.id}/read`).then(() => {
                item.is_read = true;
                this.unread = Math.max(0, this.unread - 1);
            });
        },
        markAllRead() {
            axios.post('{{ route('admin.notifications.read-all') }}').then(() => {
                this.items.forEach(i => i.is_read = true);
                this.unread = 0;
            });
        },

        {{-- Dropdown view state (Facebook-style panel) --}}
        tab: 'all',
        menu: false,
        limit: 10,
        get shown() { return this.items.filter(i => this.tab === 'all' || !i.is_read).slice(0, this.limit); },
        get more() { return this.items.filter(i => this.tab === 'all' || !i.is_read).length > this.limit; },
        get fresh() { return this.shown.filter(i => !i.is_read); },
        get earlier() { return this.shown.filter(i => i.is_read); },
        initials(name) { return name.split(' ').filter(Boolean).slice(0, 2).map(p => p[0]).join('').toUpperCase() || '?'; },
        toggle() { this.open = !this.open; this.menu = false; },
     }"
     @keydown.escape.window="open = false; menu = false">
    <div class="flex items-center justify-between gap-4">
        <h2 class="truncate font-display text-lg font-bold tracking-[-.3px] text-ink dark:text-white">@yield('title', 'Admin')</h2>

        <div class="flex items-center gap-3">
            {{-- Notifications: bell + Facebook-style dropdown anchored under it --}}
            <div class="relative" @click.outside="open = false; menu = false">
                <button type="button" @click="toggle()" class="{{ $iconBtn }}" :class="open && '!border-brand/20 !bg-brand/10 !text-brand dark:!bg-blue-500/20 dark:!text-blue-300'"
                        aria-label="Notifications" aria-haspopup="true" :aria-expanded="open.toString()" aria-controls="notif-panel">
                    <svg class="h-5 w-5" :fill="open ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span x-show="unread > 0" x-cloak x-text="unread > 99 ? '99+' : unread"
                          class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900"></span>
                </button>

                <div id="notif-panel" x-show="open" x-cloak role="dialog" aria-labelledby="notif-panel-title"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-[calc(100%+0.5rem)] z-50 flex w-[360px] origin-top-right flex-col overflow-hidden rounded-xl bg-white shadow-[0_12px_28px_rgba(22,36,79,.2),0_2px_4px_rgba(22,36,79,.1)] ring-1 ring-ink/5 max-sm:fixed max-sm:inset-x-3 max-sm:top-[4.25rem] max-sm:w-auto dark:bg-slate-800 dark:ring-white/10">

                    {{-- Header: title, "..." menu, All / Unread tabs --}}
                    <div class="px-4 pt-3 pb-2">
                        <div class="flex items-center justify-between">
                            <h3 id="notif-panel-title" class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Notifications</h3>
                            <div class="relative">
                                <button type="button" @click="menu = !menu" class="flex h-9 w-9 items-center justify-center rounded-full text-ink/60 transition hover:bg-ink/5 dark:text-slate-300 dark:hover:bg-white/10" aria-label="Notification options" :aria-expanded="menu.toString()">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                                </button>
                                <div x-show="menu" x-cloak x-transition.opacity.duration.100ms
                                     class="absolute right-0 top-full z-10 mt-1 w-56 rounded-lg bg-white p-1.5 shadow-[0_12px_28px_rgba(22,36,79,.2),0_2px_4px_rgba(22,36,79,.1)] ring-1 ring-ink/5 dark:bg-slate-700 dark:ring-white/10">
                                    <button type="button" @click="markAllRead(); menu = false" :disabled="unread === 0"
                                            class="flex w-full items-center gap-3 rounded-md px-2.5 py-2 text-left text-sm font-semibold text-ink transition hover:bg-ink/5 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent dark:text-slate-100 dark:hover:bg-white/10">
                                        <svg class="h-5 w-5 text-ink/70 dark:text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2 12.5 4.5 4.5L15 8.5M9.5 17 22 4.5"/></svg>
                                        Mark all as read
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 flex gap-1.5" role="tablist">
                            @foreach (['all' => 'All', 'unread' => 'Unread'] as $key => $label)
                                <button type="button" role="tab" @click="tab = '{{ $key }}'; limit = 10" :aria-selected="(tab === '{{ $key }}').toString()"
                                        class="rounded-full px-3 py-1.5 text-[15px] font-semibold transition"
                                        :class="tab === '{{ $key }}' ? 'bg-brand/10 text-brand-deep dark:bg-blue-500/20 dark:text-blue-300' : 'text-ink/80 hover:bg-ink/5 dark:text-slate-300 dark:hover:bg-white/10'">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    {{-- List: "New" (unread) then "Earlier" (read) --}}
                    <div class="max-h-[min(70vh,560px)] overflow-y-auto px-2 pb-2">
                        <template x-for="group in [{ label: 'New', list: fresh }, { label: 'Earlier', list: earlier }]" :key="group.label">
                            <div x-show="group.list.length">
                                <h4 class="px-2 pt-2 pb-1 text-[17px] font-bold text-ink dark:text-white" x-text="group.label"></h4>
                                <template x-for="item in group.list" :key="item.id">
                                    <button type="button" @click="markRead(item)"
                                            class="group flex w-full items-center gap-3 rounded-lg p-2 text-left transition hover:bg-ink/5 dark:hover:bg-white/5">
                                        {{-- Avatar with a type badge in the corner --}}
                                        <span class="relative shrink-0">
                                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-linear-150 from-[#3a52a0] to-ink text-base font-bold text-white" x-text="initials(item.user)"></span>
                                            <span class="absolute -right-1 -bottom-1 flex h-7 w-7 items-center justify-center rounded-full bg-brand text-white ring-2 ring-white dark:ring-slate-800" :title="item.type">
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22Zm7-6V11a7 7 0 0 0-5.5-6.84V3.5a1.5 1.5 0 0 0-3 0v.66A7 7 0 0 0 5 11v5l-1.7 1.7A1 1 0 0 0 4 19.4h16a1 1 0 0 0 .7-1.7Z"/></svg>
                                            </span>
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="line-clamp-3 text-[15px] leading-snug" :class="item.is_read ? 'text-ink/70 dark:text-slate-300' : 'text-ink dark:text-white'">
                                                <span class="font-semibold" x-text="item.user"></span>
                                                <span class="text-ink/50 dark:text-slate-400">&middot;</span>
                                                <span x-text="item.message"></span>
                                            </span>
                                            <span class="mt-0.5 block text-[13px]" :class="item.is_read ? 'text-ink/50 dark:text-slate-400' : 'font-semibold text-brand dark:text-blue-300'">
                                                <span x-text="item.time"></span> &middot; <span class="capitalize" x-text="item.type"></span>
                                            </span>
                                        </span>
                                        <span class="h-3 w-3 shrink-0 rounded-full bg-brand" x-show="!item.is_read" aria-label="Unread"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- Empty states --}}
                        <div x-show="shown.length === 0" class="flex flex-col items-center px-6 py-10 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-ink/5 text-ink/40 dark:bg-white/5 dark:text-slate-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </span>
                            <p class="mt-3 text-[15px] font-semibold text-ink dark:text-white" x-text="tab === 'unread' ? 'You\'re all caught up' : 'No notifications yet'"></p>
                            <p class="mt-0.5 text-sm text-ink/55 dark:text-slate-400" x-text="tab === 'unread' ? 'No unread notifications right now.' : 'New activity will show up here.'"></p>
                        </div>

                        <button type="button" x-show="more" @click="limit += 10"
                                class="mt-1 w-full rounded-lg bg-ink/5 px-3 py-2 text-[15px] font-semibold text-ink transition hover:bg-ink/10 dark:bg-white/5 dark:text-slate-100 dark:hover:bg-white/10">
                            See previous notifications
                        </button>
                    </div>
                </div>
            </div>

            {{-- Profile: goes to settings --}}
            <a href="{{ route('admin.settings') }}" class="flex items-center gap-2 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40" title="Account settings">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-linear-150 from-[#3a52a0] to-ink text-sm font-semibold text-white shadow-sm">{{ $navInitials }}</span>
                <span class="hidden text-sm font-semibold text-ink/80 md:block dark:text-slate-200">{{ $navName }}</span>
            </a>
        </div>
    </div>
</nav>
