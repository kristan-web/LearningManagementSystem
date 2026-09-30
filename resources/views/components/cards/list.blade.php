{{--
    Component: List Card
    A titled card wrapping an arbitrary list. Callers render their own <li> items
    into the default slot so this stays reusable across calendars, activity feeds, etc.
    Usage:
        <x-cards.list title="Upcoming" href="{{ route('calendar.index') }}" hrefLabel="View calendar">
            <li>...</li>
        </x-cards.list>

    Pass scrollable to cap the list's height and let it scroll independently of the
    page (header/link stay put) — used by the student dashboard's announcements feed.
--}}
@props(['title', 'href' => null, 'hrefLabel' => 'View all', 'empty' => 'Nothing to show.', 'scrollable' => false])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
        @if($href)
            <a href="{{ $href }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">{{ $hrefLabel }}</a>
        @endif
    </div>

    @if(trim($slot) !== '')
        <ul @class(['space-y-2', 'max-h-[32rem] overflow-y-auto pr-1 -mr-1' => $scrollable])>
            {{ $slot }}
        </ul>
    @else
        <p class="text-sm text-gray-400 dark:text-gray-500">{{ $empty }}</p>
    @endif
</div>
