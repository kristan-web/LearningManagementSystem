{{-- Shared: Inbox for students and teachers (MessageController@index) --}}
@extends(auth()->user()->role === 'Teacher' ? 'layouts.teacher' : 'layouts.student')
@section('title', auth()->user()->role === 'Teacher' ? 'Communication' : 'Inbox')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $isTeacher = auth()->user()->role === 'Teacher';
    $panel = 'rounded-2xl border border-[#e3e8f4] bg-linear-to-b from-white to-[#f7f9fe] shadow-[0_1px_2px_rgb(22_36_79/0.04),0_8px_24px_-12px_rgb(22_36_79/0.16)] dark:border-[#24386f] dark:from-[#0f1a38] dark:to-[#0f1a38] dark:shadow-none';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $initialsOf = fn ($name) => collect(explode(' ', trim($name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
@endphp

@section('content')
<div class="bento-content">
    <div class="bento-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">{{ $isTeacher ? 'Communication' : 'Inbox' }}</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">{{ $isTeacher ? 'Message the students in your classes.' : 'Messages from your teachers.' }}</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="st-btn-outline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9"/></svg>
            Announcements
        </a>
    </div>

    <div class="mx-auto grid w-full max-w-6xl grid-cols-1 gap-4 p-3 lg:grid-cols-3">
        {{-- Conversation list --}}
        <section class="{{ $panel }} flex flex-col lg:h-[38rem]">
            <div class="space-y-2 border-b border-[#eef1f8] p-4 dark:border-[#24386f]">
                {{-- New conversation: pick a contact to open the thread --}}
                @if ($newContacts->isNotEmpty())
                    <form method="GET" action="{{ route($inboxRoute) }}" class="flex gap-2">
                        <label for="new-with" class="sr-only">New message to</label>
                        <select id="new-with" name="with" required class="{{ $input }}">
                            <option value="" disabled selected>New message to…</option>
                            @foreach ($newContacts as $contact)
                                <option value="{{ $contact->user_id }}">{{ $contact->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-navy shrink-0 !px-3" aria-label="Open conversation">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
                        </button>
                    </form>
                @endif
                <form method="GET" action="{{ route($inboxRoute) }}" class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink/45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/></svg>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search messages…" aria-label="Search messages" class="{{ $input }} pl-9">
                </form>
            </div>
            <ul class="flex-1 space-y-1 overflow-y-auto p-2">
                @forelse ($conversations as $conversation)
                    @php $isOpen = $activeConversation && $activeConversation->user_id === $conversation->user_id; @endphp
                    <li>
                        <a href="{{ route($inboxRoute, ['with' => $conversation->user_id]) }}"
                           class="flex items-center gap-3 rounded-xl p-2.5 transition {{ $isOpen ? 'bg-white shadow-sm ring-1 ring-ink/[.06] dark:bg-white/10 dark:ring-white/10' : 'hover:bg-white/70 dark:hover:bg-white/5' }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-linear-150 from-[#3a52a0] to-ink text-xs font-semibold text-white">{{ $initialsOf($conversation->name) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm {{ $conversation->unread ? 'font-bold' : 'font-semibold' }} text-ink dark:text-white">{{ $conversation->name }}</span>
                                    <span class="shrink-0 text-[11px] text-ink/45 dark:text-slate-500">{{ $conversation->last_sent_at?->diffForHumans(short: true) }}</span>
                                </span>
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-xs {{ $conversation->unread ? 'font-semibold text-ink/80 dark:text-slate-200' : 'text-ink/60 dark:text-slate-400' }}">{{ $conversation->last_message }}</span>
                                    @if ($conversation->unread)
                                        <span class="flex h-5 min-w-5 shrink-0 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white">{{ $conversation->unread }}</span>
                                    @endif
                                </span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="flex flex-col items-center px-4 py-10 text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-ink/5 text-ink/40 dark:bg-white/5 dark:text-slate-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h3.439a.991.991 0 0 1 .908.6 3.978 3.978 0 0 0 7.306 0 .99.99 0 0 1 .908-.6H20M4 13v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6M4 13l2-9h12l2 9"/></svg>
                        </span>
                        <p class="mt-3 text-sm font-semibold text-ink dark:text-white">{{ request('search') ? 'No matches' : 'No conversations yet' }}</p>
                        <p class="mt-0.5 text-xs text-ink/60 dark:text-slate-400">
                            {{ request('search') ? 'Try another name or word.' : ($newContacts->isNotEmpty() ? 'Pick someone above to start a conversation.' : ($isTeacher ? 'Students appear here once they are enrolled in your classes.' : 'Your teachers appear here once you are enrolled in a section.')) }}
                        </p>
                    </li>
                @endforelse
            </ul>
        </section>

        {{-- Thread --}}
        <section class="{{ $panel }} flex min-h-[28rem] flex-col lg:col-span-2 lg:h-[38rem]">
            @if ($activeConversation)
                <div class="flex items-center gap-3 border-b border-[#eef1f8] px-5 py-4 dark:border-[#24386f]">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-linear-150 from-[#3a52a0] to-ink text-xs font-semibold text-white">{{ $initialsOf($activeConversation->name) }}</span>
                    <div class="min-w-0">
                        <p class="truncate font-display text-base font-bold text-ink dark:text-white">{{ $activeConversation->name }}</p>
                        <p class="text-xs text-ink/60 dark:text-slate-400">{{ $activeConversation->role }}</p>
                    </div>
                </div>
                <div class="flex flex-1 flex-col-reverse overflow-y-auto px-5 py-4">
                    {{-- column-reverse keeps the newest message in view without any script --}}
                    <div class="space-y-3">
                        @forelse ($messages as $message)
                            <div class="flex {{ $message->is_mine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm {{ $message->is_mine ? 'rounded-br-md bg-linear-150 from-[#3a52a0] to-ink text-white' : 'rounded-bl-md bg-white text-ink ring-1 ring-ink/10 dark:bg-slate-800 dark:text-slate-100 dark:ring-white/10' }}">
                                    @if ($message->body !== '')
                                        <p class="whitespace-pre-line break-words">{{ $message->body }}</p>
                                    @endif
                                    @if ($message->attachment_name)
                                        <a href="{{ route('messages.attachment', $message->id) }}" class="mt-1 flex items-center gap-2 rounded-lg px-2.5 py-2 text-xs font-semibold transition {{ $message->is_mine ? 'bg-white/10 text-white hover:bg-white/20' : 'bg-[#eef3fd] text-brand-deep hover:bg-[#e2eafb] dark:bg-blue-500/15 dark:text-blue-300' }}">
                                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13"/></svg>
                                            <span class="truncate">{{ $message->attachment_name }}</span>
                                        </a>
                                    @endif
                                    <p class="mt-1 text-[11px] {{ $message->is_mine ? 'text-white/60' : 'text-ink/45 dark:text-slate-500' }}">{{ $message->sent_at?->format('M j, g:i A') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="py-10 text-center text-sm text-ink/55 dark:text-slate-400">No messages yet. Say hello to {{ $activeConversation->name }}.</p>
                        @endforelse
                    </div>
                </div>

                <form method="POST" action="{{ route('messages.store') }}" enctype="multipart/form-data" x-data="{ file: '' }" class="border-t border-[#eef1f8] p-3 dark:border-[#24386f]">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $activeConversation->user_id }}">
                    @if ($errors->has('body') || $errors->has('attachment'))
                        <p class="mb-2 text-xs font-medium text-red-600 dark:text-red-400">{{ $errors->first('body') ?: $errors->first('attachment') }}</p>
                    @endif
                    <p x-show="file" x-cloak class="mb-2 flex items-center gap-2 text-xs text-ink/70 dark:text-slate-300">
                        <svg class="h-4 w-4 shrink-0 text-brand dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13"/></svg>
                        <span class="truncate font-semibold" x-text="file"></span>
                        <button type="button" @click="file = ''; $refs.file.value = ''" class="font-semibold text-red-600 hover:underline dark:text-red-400">Remove</button>
                    </p>
                    <div class="flex items-end gap-2">
                        <label class="st-btn-outline shrink-0 cursor-pointer !px-3" title="Attach a file (PDF, Office, image, TXT or ZIP, up to 10 MB)">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13"/></svg>
                            <span class="sr-only">Attach a file</span>
                            <input type="file" name="attachment" x-ref="file" class="sr-only" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.jpg,.jpeg,.png"
                                   @change="file = $event.target.files[0]?.name || ''">
                        </label>
                        <label for="message-body" class="sr-only">Message</label>
                        <textarea id="message-body" name="body" rows="2" maxlength="2000" placeholder="Write a message…" class="{{ $input }} min-h-[2.625rem] resize-none">{{ old('body') }}</textarea>
                        <button type="submit" class="btn-navy shrink-0">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2Zm0 0v-8"/></svg>
                            Send
                        </button>
                    </div>
                </form>
            @else
                <div class="flex flex-1 flex-col items-center justify-center px-6 py-10 text-center">
                    <span class="st-icon !h-14 !w-14 !rounded-2xl"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5l-5 5v-5Z"/></svg></span>
                    <p class="mt-4 font-display text-lg font-bold text-ink dark:text-white">Select a conversation</p>
                    <p class="mt-1 max-w-sm text-sm text-ink/60 dark:text-slate-400">Choose a conversation on the left, or start a new one.</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
