{{--
    Partial: Portal sidebar (student + teacher), same look and collapse behaviour as the admin sidebar.
    Params:
      $roleLabel  'Student' | 'Teacher'
      $homeRoute  route name of the dashboard
      $group      ['label' => ..., 'icon' => path, 'links' => [['label','href','active'], ...]]  collapsible menu
      $mainLinks  [['label','href','active','icon'], ...]
      $helpLinks  [['label','href','active','icon'], ...]
--}}
@php
   $user = auth()->user();
   $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $roleLabel;
   $initials = collect(explode(' ', trim($userName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

   // Icon tile + label; active = white card, navy icon tile, gold dot.
   $linkBase = 'sb-link group relative flex items-center gap-3 rounded-xl px-2 py-1.5 text-sm font-medium transition-colors duration-150';
   $linkIdle = 'text-ink/65 hover:bg-ink/[.04] hover:text-ink dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white';
   $linkActive = 'is-active bg-white font-semibold text-ink shadow-[0_1px_2px_rgba(22,36,79,.06),0_6px_16px_-6px_rgba(22,36,79,.18)] ring-1 ring-ink/[.06] after:ml-auto after:mr-1.5 after:h-1.5 after:w-1.5 after:shrink-0 after:rounded-full after:bg-gold dark:bg-white/10 dark:text-white dark:shadow-none dark:ring-white/10';
   $tip = 'sb-tip rounded-lg bg-ink px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700';
   $subLink = 'relative flex items-center rounded-lg py-2 pl-[3.25rem] pr-3 text-sm transition-colors duration-150 before:absolute before:left-[1.3125rem] before:top-1/2 before:h-1.5 before:w-1.5 before:-translate-y-1/2 before:rounded-full';
   $subIdle = 'text-ink/60 hover:bg-ink/[.04] hover:text-ink before:bg-ink/20 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white dark:before:bg-slate-600';
   $subActive = 'font-semibold text-ink before:bg-ink before:ring-4 before:ring-ink/10 dark:text-white dark:before:bg-blue-400 dark:before:ring-blue-400/20';

   $groupActive = collect($group['links'])->contains('active', true);
@endphp

<style>
   /* Icon tile: plain icon when idle, white tile on hover, navy tile when active (same as admin sidebar) */
   .sb-ico {
      box-sizing: content-box; flex: none; width: 1.125rem; height: 1.125rem; padding: .4375rem;
      border-radius: .625rem; color: rgb(22 36 79 / .55);
      transition: background-color .15s ease, color .15s ease, box-shadow .15s ease;
   }
   .sb-link:hover .sb-ico { background: #fff; color: #16244f; box-shadow: 0 0 0 1px rgb(22 36 79 / .07), 0 1px 2px rgb(22 36 79 / .08); }
   .sb-link.is-active .sb-ico {
      background: linear-gradient(150deg, #3a52a0, #16244f 78%); color: #fff;
      box-shadow: inset 0 1px 0 rgb(255 255 255 / .2), 0 3px 8px -2px rgb(22 36 79 / .45);
   }
   .sb-logout .sb-ico, .sb-logout:hover .sb-ico { color: currentColor; background: transparent; box-shadow: none; }
   html.dark .sb-ico { color: rgb(203 213 225); }
   html.dark .sb-link:hover .sb-ico { background: rgb(255 255 255 / .08); color: #fff; box-shadow: none; }
   html.dark .sb-link.is-active .sb-ico { color: #fff; }

   /* Collapsed sidebar (desktop only), same rules as the admin sidebar */
   .sb-label { opacity: 1; transition: opacity .15s ease; overflow: hidden; white-space: nowrap; }
   .sb-tip, .sb-show { display: none; }
   #separator-sidebar { contain: layout style; }

   @media (min-width: 640px) {
      html.sb-collapsed .sm\:ml-64 { margin-left: 5rem; }

      .sb-burger { left: 14.875rem; }
      html.sb-collapsed .sb-burger { left: 3.875rem; }

      #separator-sidebar[data-collapsed="true"] { width: 5rem; }
      #separator-sidebar[data-collapsed="true"] .sb-label { max-width: 0; opacity: 0; }
      #separator-sidebar[data-collapsed="true"] .sb-link::after { display: none; }
      #separator-sidebar[data-collapsed="true"] .sb-hide { display: none; }
      #separator-sidebar[data-collapsed="true"] .sb-show { display: block; }
      #separator-sidebar[data-collapsed="true"] .sb-link { justify-content: center; gap: 0; padding-inline: 0; }
      #separator-sidebar[data-collapsed="true"] .sb-heading { max-height: 0; opacity: 0; margin: 0; padding: 0; }
      #separator-sidebar[data-collapsed="true"] .sb-nav { overflow: visible; }
      #separator-sidebar[data-collapsed="true"] .sb-center { justify-content: center; gap: 0; }
      #separator-sidebar[data-collapsed="true"] .sb-divider { display: block; }

      #separator-sidebar[data-collapsed="true"] .sb-tip {
         display: block; position: absolute; left: calc(100% + .75rem); top: 50%; z-index: 60;
         transform: translate(-.25rem, -50%); opacity: 0; pointer-events: none; white-space: nowrap;
         transition: opacity .15s ease, transform .15s ease;
      }
      #separator-sidebar[data-collapsed="true"] .group:hover > .sb-tip,
      #separator-sidebar[data-collapsed="true"] .group:focus-visible > .sb-tip { opacity: 1; transform: translate(0, -50%); }
   }
</style>

<div class="contents"
     x-data="{
        collapsed: false,
        mobileOpen: false,
        isDesktop: window.innerWidth >= 640,
        groupOpen: {{ $groupActive ? 'true' : 'false' }},
        get isOpen() { return this.isDesktop ? !this.collapsed : this.mobileOpen },
        toggle() { if (this.isDesktop) this.collapsed = !this.collapsed; else this.mobileOpen = !this.mobileOpen; },
        openGroup() {
           if (this.isDesktop && this.collapsed) { this.collapsed = false; this.groupOpen = true; }
           else this.groupOpen = !this.groupOpen;
        }
     }"
     x-effect="document.documentElement.classList.toggle('sb-collapsed', collapsed && isDesktop)"
     @resize.window.debounce.100ms="isDesktop = window.innerWidth >= 640; if (isDesktop) mobileOpen = false"
     @keydown.escape.window="mobileOpen = false">

   {{-- Mobile backdrop --}}
   <div x-show="mobileOpen" x-cloak
        x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        class="fixed inset-0 z-30 bg-ink/45 sm:hidden"></div>

   {{-- Portal Sidebar Component --}}
   <aside id="separator-sidebar"
          :data-collapsed="collapsed ? 'true' : null"
          class="fixed top-0 left-0 z-40 h-screen w-64 -translate-x-full sm:translate-x-0 transition-[width,transform] duration-200 ease-out"
          :class="{ '-translate-x-full': !mobileOpen, 'translate-x-0 shadow-2xl': mobileOpen }"
          aria-label="Sidebar">
      <div class="flex h-full flex-col border-e border-ink/10 bg-linear-to-b from-white via-[#f7f9fe] to-[#e9eefa] dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950">

         {{-- Brand --}}
         <div class="sb-center flex h-18 shrink-0 items-center border-b border-ink/10 px-4 dark:border-slate-800">
            <a href="{{ route($homeRoute) }}" class="flex items-center">
               <img src="{{ asset('images/Enrollment logo.png') }}" alt="Enrollment Management System" class="sb-hide h-16 w-auto shrink-0">
               <img src="{{ asset('images/Enrollment logo.png') }}" alt="Enrollment Management System" class="sb-show h-14 w-auto shrink-0">
            </a>
         </div>

         {{-- Navigation --}}
         <nav class="sb-nav flex-1 overflow-y-auto overflow-x-hidden px-3 py-4">
            <p class="sb-heading mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-ink/40 transition-opacity duration-150 dark:text-slate-500">Main Menu</p>

            <ul class="space-y-1">
               <li>
                  <a href="{{ route($homeRoute) }}" class="{{ $linkBase }} {{ request()->routeIs($homeRoute) ? $linkActive : $linkIdle }}">
                     <svg class="sb-ico" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/></svg>
                     <span class="sb-label">Dashboard</span>
                     <span class="{{ $tip }}">Dashboard</span>
                  </a>
               </li>

               {{-- Collapsible menu group --}}
               <li>
                  <button type="button" @click="openGroup()" :aria-expanded="groupOpen" aria-controls="dropdown-group"
                          class="{{ $linkBase }} w-full {{ $groupActive ? 'is-active font-semibold text-ink hover:bg-ink/[.04] dark:text-white dark:hover:bg-white/5' : $linkIdle }}">
                     <svg class="sb-ico" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $group['icon'] }}"/></svg>
                     <span class="sb-label flex-1 text-left">{{ $group['label'] }}</span>
                     <svg class="sb-hide h-4 w-4 shrink-0 transition-transform duration-300" :class="groupOpen && 'rotate-180'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                     <span class="{{ $tip }}">{{ $group['label'] }}</span>
                  </button>
                  <div id="dropdown-group" class="sb-hide grid transition-[grid-template-rows] duration-300 ease-in-out {{ $groupActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}"
                       :class="groupOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                     <ul class="relative space-y-0.5 overflow-hidden before:absolute before:left-[calc(1.5rem-0.5px)] before:top-3 before:bottom-3 before:w-px before:bg-ink/15 dark:before:bg-slate-700">
                        @foreach ($group['links'] as $item)
                           <li class="{{ $loop->first ? 'pt-1' : '' }}">
                              <a href="{{ $item['href'] }}" class="{{ $subLink }} {{ $item['active'] ? $subActive : $subIdle }}">{{ $item['label'] }}</a>
                           </li>
                        @endforeach
                     </ul>
                  </div>
               </li>

               @foreach ($mainLinks as $item)
                  <li>
                     <a href="{{ $item['href'] }}" class="{{ $linkBase }} {{ $item['active'] ? $linkActive : $linkIdle }}">
                        <svg class="sb-ico" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                        <span class="sb-label">{{ $item['label'] }}</span>
                        <span class="{{ $tip }}">{{ $item['label'] }}</span>
                     </a>
                  </li>
               @endforeach
            </ul>

            <p class="sb-heading mb-2 mt-6 px-3 text-[11px] font-bold uppercase tracking-wider text-ink/40 transition-opacity duration-150 dark:text-slate-500">Help</p>
            <div class="sb-divider mx-3 my-4 hidden border-t border-ink/10 dark:border-slate-800"></div>

            <ul class="space-y-1">
               @foreach ($helpLinks as $item)
                  <li>
                     <a href="{{ $item['href'] }}" class="{{ $linkBase }} {{ $item['active'] ? $linkActive : $linkIdle }}">
                        <svg class="sb-ico" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                        <span class="sb-label">{{ $item['label'] }}</span>
                        <span class="{{ $tip }}">{{ $item['label'] }}</span>
                     </a>
                  </li>
               @endforeach
            </ul>
         </nav>

         {{-- Footer: profile, theme toggle, logout --}}
         <div class="shrink-0 space-y-2 border-t border-ink/10 bg-white/60 p-3 dark:border-slate-800 dark:bg-slate-950/30">
            {{-- Profile card --}}
            <a href="{{ route('profile.edit') }}" class="sb-center group relative flex items-center gap-3 rounded-xl bg-white/70 px-2.5 py-2 ring-1 ring-ink/10 transition hover:bg-white hover:shadow-sm dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10">
               <span class="relative shrink-0">
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-linear-150 from-[#3a52a0] to-ink text-xs font-semibold text-white shadow-sm ring-2 ring-white dark:ring-slate-800">{{ $initials ?: mb_substr($roleLabel, 0, 1) }}</span>
                  <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900" aria-hidden="true"></span>
               </span>
               <span class="sb-label flex min-w-0 flex-1 flex-col leading-tight">
                  <span class="truncate text-sm font-semibold text-ink dark:text-white">{{ $userName }}</span>
                  <span class="truncate text-xs text-ink/55 dark:text-slate-400">{{ $roleLabel }}</span>
               </span>
               <span class="{{ $tip }}">My Profile</span>
            </a>

            {{-- Light / dark mode switch (uses `dark` from the root layout's x-data) --}}
            <button type="button" @click="dark = !dark" role="switch" :aria-checked="dark.toString()"
                    :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
                    class="group relative flex w-full items-center rounded-xl bg-slate-900/5 p-1 text-xs font-medium ring-1 ring-inset ring-slate-900/5 transition-colors duration-200 hover:bg-slate-900/10 dark:bg-white/5 dark:ring-white/10 dark:hover:bg-white/10">
               {{-- Sliding thumb --}}
               <span class="sb-hide absolute inset-y-1 left-1 w-[calc(50%-0.25rem)] rounded-lg bg-white shadow-sm ring-1 ring-slate-900/5 transition-transform duration-300 ease-out dark:bg-slate-700 dark:ring-white/10" :class="dark ? 'translate-x-full' : 'translate-x-0'"></span>

               <span class="sb-hide relative z-10 flex flex-1 items-center justify-center gap-1.5 py-1.5 transition-colors duration-300" :class="dark ? 'text-slate-500' : 'text-amber-600'">
                  <svg class="h-4 w-4 transition-transform duration-500 group-hover:rotate-45" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z"/></svg>
                  Light
               </span>
               <span class="sb-hide relative z-10 flex flex-1 items-center justify-center gap-1.5 py-1.5 transition-colors duration-300" :class="dark ? 'text-indigo-300' : 'text-slate-500'">
                  <svg class="h-4 w-4 transition-transform duration-500 group-hover:-rotate-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd"/></svg>
                  Dark
               </span>

               {{-- Collapsed: single icon showing the mode you'd switch to --}}
               <span class="sb-show relative mx-auto h-8 w-8">
                  <svg class="absolute inset-1.5 h-5 w-5 text-brand transition-[opacity,transform] duration-500" :class="dark ? 'rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd"/></svg>
                  <svg class="absolute inset-1.5 h-5 w-5 text-amber-300 transition-[opacity,transform] duration-500" :class="dark ? 'rotate-0 scale-100 opacity-100' : '-rotate-90 scale-0 opacity-0'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z"/></svg>
               </span>
               <span class="{{ $tip }}" x-text="dark ? 'Light mode' : 'Dark mode'"></span>
            </button>

            <form method="POST" action="{{ route('logout') }}"
                  data-confirm="Log out?" data-confirm-text="You will need to sign in again to use the portal." data-confirm-button="Log out">
               @csrf
               <button type="submit" class="{{ $linkBase }} sb-logout w-full text-red-600 hover:bg-red-50 hover:text-red-700 hover:shadow-sm dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                  <svg class="sb-ico" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2"/></svg>
                  <span class="sb-label">Logout</span>
                  <span class="{{ $tip }}">Logout</span>
               </button>
            </form>
         </div>
      </div>
   </aside>

   {{-- Animated burger toggle, pinned to the sidebar edge (same as admin) --}}
   <button type="button" @click="toggle()" x-cloak
           :aria-expanded="isOpen" aria-controls="separator-sidebar"
           :aria-label="isOpen ? 'Collapse sidebar' : 'Expand sidebar'"
           class="sb-burger fixed top-5 z-50 flex h-9 w-9 items-center justify-center rounded-full border border-ink/10 bg-white text-ink/70 shadow-md transition-[left,color,box-shadow,transform] duration-200 ease-out hover:scale-110 hover:text-brand hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 dark:border-slate-700 dark:bg-slate-800/90 dark:text-slate-300 dark:hover:text-white"
           :class="mobileOpen ? 'left-[14.875rem]' : 'left-4'">
      <span class="relative block h-4 w-4">
         <span class="absolute left-0 top-1/2 h-0.5 w-4 -mt-px rounded-full bg-current transition-transform duration-300 ease-in-out" :class="isOpen ? 'rotate-45' : '-translate-y-1.5'"></span>
         <span class="absolute left-0 top-1/2 h-0.5 w-4 -mt-px rounded-full bg-current transition-[opacity,transform] duration-200 ease-in-out" :class="isOpen ? 'scale-x-0 opacity-0' : 'scale-x-100 opacity-100'"></span>
         <span class="absolute left-0 top-1/2 h-0.5 w-4 -mt-px rounded-full bg-current transition-transform duration-300 ease-in-out" :class="isOpen ? '-rotate-45' : 'translate-y-1.5'"></span>
      </span>
   </button>
</div>
