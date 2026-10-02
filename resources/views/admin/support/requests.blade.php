{{-- Admin: Support requests sent from the student, teacher and admin Support pages (SupportRequestController@index) --}}
@extends('layouts.admin')
@section('title', 'Support Requests')

@php
    $card = 'rounded-2xl border border-blue-100 bg-linear-to-b from-white to-sky-50/60 shadow-[0_12px_32px_-16px_rgb(37_99_235/0.25)] transition-colors duration-300 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 dark:shadow-none';
    $priorityBadge = [
        'Low' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Normal' => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
        'High' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        'Urgent' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
    ];
    $tabs = ['Open' => 'Open', 'Resolved' => 'Resolved', 'All' => 'All'];
@endphp

@section('content')
<div class="mx-auto w-full max-w-6xl space-y-6 pt-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('admin.support') }}" class="inline-flex items-center gap-1 text-sm font-medium text-ink/60 transition hover:text-brand dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to support
            </a>
            <h1 class="mt-2 text-2xl font-bold text-ink dark:text-white">Support Requests</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Requests sent from the Support page by students, teachers and admins.</p>
        </div>

        {{-- Status tabs --}}
        <nav class="inline-flex rounded-xl border border-blue-100 bg-white p-1 dark:border-slate-700 dark:bg-slate-900" aria-label="Filter by status">
            @foreach ($tabs as $key => $tabLabel)
                <a href="{{ route('admin.support.requests', ['status' => $key]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === $key ? 'bg-navy text-white shadow-sm' : 'text-ink/60 hover:text-ink dark:text-slate-400 dark:hover:text-white' }}"
                   @if ($status === $key) aria-current="page" @endif>
                    {{ $tabLabel }}
                    <span class="rounded-full px-1.5 text-[11px] {{ $status === $key ? 'bg-white/20' : 'bg-[#eef3fd] text-brand-deep dark:bg-blue-500/15 dark:text-blue-300' }}">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    <div class="space-y-4">
        @forelse ($requests as $item)
            @php $from = $item->user ? trim($item->user->first_name . ' ' . $item->user->last_name) : 'Deleted user'; @endphp
            <article class="{{ $card }} px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-[#eef3fd] px-2.5 py-0.5 text-xs font-semibold text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">{{ $item->category }}</span>
                            @if ($item->priority)
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $priorityBadge[$item->priority] ?? '' }}">{{ $item->priority }}</span>
                            @endif
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $item->status === 'Resolved' ? 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'bg-gold/20 text-gold-deep dark:bg-gold/15 dark:text-gold' }}">{{ $item->status }}</span>
                        </div>
                        <h2 class="mt-2 text-base font-semibold text-ink dark:text-white">{{ $item->subject }}</h2>
                        <p class="mt-0.5 text-xs text-ink/55 dark:text-slate-400">
                            {{ $from }}@if ($item->user) · {{ $item->user->role }} · <a href="mailto:{{ $item->user->email }}" class="font-medium text-brand hover:underline dark:text-blue-300">{{ $item->user->email }}</a>@endif
                            · {{ $item->created_at?->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.support.requests.update', $item->support_request_id) }}" class="shrink-0"
                          data-confirm="{{ $item->status === 'Resolved' ? 'Reopen this request?' : 'Mark this request as resolved?' }}"
                          data-confirm-button="{{ $item->status === 'Resolved' ? 'Reopen' : 'Resolve' }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $item->status === 'Resolved' ? 'Open' : 'Resolved' }}">
                        @if ($item->status === 'Resolved')
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-ink/80 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Reopen</button>
                        @else
                            <button type="submit" class="btn-navy">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Resolve
                            </button>
                        @endif
                    </form>
                </div>

                <p class="mt-3 whitespace-pre-line text-sm text-ink/80 dark:text-slate-300">{{ $item->message }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink/55 dark:text-slate-400">
                    @if ($item->attachment_path)
                        <a href="{{ route('admin.support.requests.attachment', $item->support_request_id) }}" class="inline-flex items-center gap-1 font-semibold text-brand hover:underline dark:text-blue-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 1 0 2.828 2.828l6.414-6.586a4 4 0 0 0-5.656-5.656l-6.415 6.585a6 6 0 1 0 8.486 8.486L20.5 13"/></svg>
                            {{ $item->attachment_name ?? 'Screenshot' }}
                        </a>
                    @endif
                    @if ($item->resolved_at)
                        <span>Resolved {{ $item->resolved_at->format('M d, Y h:i A') }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="{{ $card }} px-6 py-14 text-center">
                <p class="text-base font-semibold text-ink dark:text-white">No {{ $status === 'All' ? '' : strtolower($status) . ' ' }}requests</p>
                <p class="mt-1 text-sm text-ink/60 dark:text-slate-400">Requests sent from any Support page show up here.</p>
            </div>
        @endforelse
    </div>

    @if ($requests->hasPages())
        <div>{{ $requests->links() }}</div>
    @endif
</div>
@endsection
