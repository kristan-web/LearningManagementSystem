{{-- Admin: Reports & Analytics --}}
@extends('layouts.admin')
@section('title', 'Reports & Analytics')

@php
    $card = 'rounded-xl border border-blue-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800';
    $th = 'px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-ink/60 dark:text-gray-400';
    $td = 'px-4 py-3 text-sm text-ink/80 dark:text-gray-300';
    $input = 'block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-ink focus:border-brand focus:ring-brand dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400';

    // Badge color by action keyword
    $actionBadge = function (string $action) {
        $a = strtolower($action);
        return match (true) {
            str_contains($a, 'delete') || str_contains($a, 'remove') => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
            str_contains($a, 'create') || str_contains($a, 'add') || str_contains($a, 'insert') => 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-300',
            str_contains($a, 'update') || str_contains($a, 'edit') => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
            str_contains($a, 'login') || str_contains($a, 'logout') => 'bg-blue-100 text-brand-deep dark:bg-blue-500/15 dark:text-blue-300',
            default => 'bg-gray-100 text-ink/80 dark:bg-gray-700 dark:text-gray-300',
        };
    };

    $hasFilters = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

@section('content')
    <div class="mx-auto max-w-6xl space-y-6">
        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-ink dark:text-white">Reports & Analytics</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">System activity based on audit logs.</p>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.reports.index') }}" class="{{ $card }} p-4">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div class="lg:col-span-2">
                    <label for="search" class="mb-1 block text-xs font-medium text-ink/60 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="User, action or entity..." class="{{ $input }}">
                </div>
                <div>
                    <label for="action" class="mb-1 block text-xs font-medium text-ink/60 dark:text-gray-400">Action</label>
                    <select id="action" name="action" class="{{ $input }}">
                        <option value="">All actions</option>
                        @foreach ($actionOptions as $option)
                            <option value="{{ $option }}" @selected(($filters['action'] ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="entity_type" class="mb-1 block text-xs font-medium text-ink/60 dark:text-gray-400">Entity</label>
                    <select id="entity_type" name="entity_type" class="{{ $input }}">
                        <option value="">All entities</option>
                        @foreach ($entityOptions as $option)
                            <option value="{{ $option }}" @selected(($filters['entity_type'] ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-ink/60 dark:text-gray-400">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="{{ $input }}">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-ink/60 dark:text-gray-400">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="{{ $input }}">
                </div>
            </div>
            <div class="mt-3 flex justify-end gap-2">
                @if ($hasFilters)
                    <a href="{{ route('admin.reports.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-ink/80 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Clear</a>
                @endif
                <button type="submit" class="btn-navy">Apply Filters</button>
            </div>
        </form>

        {{-- Summary --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total Activities', 'value' => $totalLogs, 'color' => 'text-brand dark:text-blue-400'],
                ['label' => 'Today', 'value' => $todayLogs, 'color' => 'text-green-600 dark:text-green-400'],
                ['label' => 'Active Users', 'value' => $activeUsers, 'color' => 'text-violet-600 dark:text-violet-400'],
                ['label' => 'Entity Types', 'value' => $entityTypeCount, 'color' => 'text-amber-600 dark:text-amber-400'],
            ] as $stat)
                <div class="{{ $card }} p-4">
                    <p class="text-xs font-medium uppercase tracking-wider text-ink/60 dark:text-gray-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $stat['color'] }}">{{ number_format($stat['value']) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Analysis tables --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- By action --}}
            <div class="{{ $card }} overflow-hidden">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-ink dark:text-white">Activity by Action</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="{{ $th }}">Action</th>
                                <th scope="col" class="{{ $th }} text-right">Count</th>
                                <th scope="col" class="{{ $th }} text-right">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($logsByAction as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="{{ $td }}">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $actionBadge($row->action) }}">{{ $row->action }}</span>
                                    </td>
                                    <td class="{{ $td }} text-right font-medium">{{ number_format($row->total) }}</td>
                                    <td class="{{ $td }} text-right">{{ $totalLogs ? number_format($row->total / $totalLogs * 100, 1) : 0 }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="{{ $td }} text-center text-ink/45">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- By entity --}}
            <div class="{{ $card }} overflow-hidden">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-ink dark:text-white">Activity by Entity</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="{{ $th }}">Entity</th>
                                <th scope="col" class="{{ $th }} text-right">Records</th>
                                <th scope="col" class="{{ $th }} text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($logsByEntity as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="{{ $td }} font-medium">{{ $row->entity_type }}</td>
                                    <td class="{{ $td }} text-right">{{ number_format($row->records) }}</td>
                                    <td class="{{ $td }} text-right font-medium">{{ number_format($row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="{{ $td }} text-center text-ink/45">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top users --}}
            <div class="{{ $card }} overflow-hidden">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-ink dark:text-white">Most Active Users</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="{{ $th }}">User</th>
                                <th scope="col" class="{{ $th }} text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($topUsers as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="{{ $td }}">
                                        <p class="font-medium text-ink dark:text-white">{{ trim($row->first_name . ' ' . $row->last_name) }}</p>
                                        <p class="text-xs text-ink/60 dark:text-gray-400">{{ $row->role }} &middot; last {{ \Illuminate\Support\Carbon::parse($row->last_activity)->diffForHumans() }}</p>
                                    </td>
                                    <td class="{{ $td }} text-right font-medium">{{ number_format($row->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="{{ $td }} text-center text-ink/45">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Audit log table --}}
        <div class="{{ $card }} overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-semibold text-ink dark:text-white">Audit Logs</h2>
                    <p class="text-xs text-ink/60 dark:text-gray-400">Detailed record of system activity.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-brand-deep dark:bg-blue-500/15 dark:text-blue-300">{{ number_format($logs->total()) }} records</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="{{ $th }}">#</th>
                            <th scope="col" class="{{ $th }}">User</th>
                            <th scope="col" class="{{ $th }}">Role</th>
                            <th scope="col" class="{{ $th }}">Action</th>
                            <th scope="col" class="{{ $th }}">Entity</th>
                            <th scope="col" class="{{ $th }}">Entity ID</th>
                            <th scope="col" class="{{ $th }}">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($logs as $log)
                            @php $time = \Illuminate\Support\Carbon::parse($log->timestamp); @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="{{ $td }} text-ink/45">{{ $log->log_id }}</td>
                                <td class="{{ $td }}">
                                    @if ($log->user_id && $log->first_name !== null)
                                        <p class="font-medium text-ink dark:text-white">{{ trim($log->first_name . ' ' . $log->last_name) }}</p>
                                        <p class="text-xs text-ink/60 dark:text-gray-400">{{ $log->email }}</p>
                                    @else
                                        <span class="italic text-ink/45">System / Deleted user</span>
                                    @endif
                                </td>
                                <td class="{{ $td }}">{{ $log->role ?? '—' }}</td>
                                <td class="{{ $td }}">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $actionBadge($log->action) }}">{{ $log->action }}</span>
                                </td>
                                <td class="{{ $td }}">{{ $log->entity_type }}</td>
                                <td class="{{ $td }}">{{ $log->entity_id }}</td>
                                <td class="{{ $td }} whitespace-nowrap">
                                    <p>{{ $time->format('M d, Y h:i A') }}</p>
                                    <p class="text-xs text-ink/60 dark:text-gray-400">{{ $time->diffForHumans() }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-ink/45">
                                    {{ $hasFilters ? 'No audit logs match the selected filters.' : 'No audit logs recorded yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
