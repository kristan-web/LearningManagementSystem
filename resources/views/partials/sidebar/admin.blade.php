@php
   $user = auth()->user();
   $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Administrator';
   $initials = collect(explode(' ', trim($userName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

   $linkBase = 'sb-link group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200';
   $linkIdle = 'text-slate-600 hover:bg-white/80 hover:text-blue-700 hover:shadow-sm dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white';
   $linkActive = 'bg-white text-blue-700 shadow-md shadow-blue-900/5 ring-1 ring-blue-100 dark:bg-blue-500/15 dark:text-white dark:shadow-none dark:ring-blue-400/20 before:absolute before:-left-3 before:top-1/2 before:h-6 before:w-1 before:-translate-y-1/2 before:rounded-r-full before:bg-linear-to-b before:from-sky-400 before:to-blue-600';
   $iconBase = 'shrink-0 w-5 h-5 transition-transform duration-200 group-hover:scale-110';
   $subLink = 'relative flex items-center rounded-lg py-2 pl-11 pr-3 text-sm transition-colors duration-200 before:absolute before:left-[1.35rem] before:top-1/2 before:h-1.5 before:w-1.5 before:-translate-y-1/2 before:rounded-full before:transition-colors';
   $subIdle = 'text-slate-500 hover:bg-white/60 hover:text-blue-700 before:bg-slate-300 dark:text-slate-400 dark:hover:bg-white/5 dark:hover:text-white dark:before:bg-slate-600';
   $subActive = 'font-medium text-blue-700 bg-white/60 before:bg-blue-600 before:ring-4 before:ring-blue-100 dark:bg-white/5 dark:text-white dark:before:bg-blue-400 dark:before:ring-blue-400/20';

   $isActive = fn (...$patterns) => request()->is(...$patterns);
   $academicActive = $isActive('admin/curriculum*', 'admin/school-year*', 'admin/enrollment*');
@endphp

<style>
   /* Labels, headings and submenus fold away when the sidebar is collapsed (desktop only). */
   .sb-label { max-width: 12rem; opacity: 1; transition: max-width .3s ease, opacity .2s ease; overflow: hidden; white-space: nowrap; }
   .sb-tip, .sb-show { display: none; }

   @media (min-width: 640px) {
      body:has(#separator-sidebar) .sm\:ml-64 { transition: margin-left .3s cubic-bezier(.4, 0, .2, 1); }
      body:has(#separator-sidebar[data-collapsed="true"]) .sm\:ml-64 { margin-left: 5rem; }

      .sb-burger { left: 14.875rem; }
      body:has(#separator-sidebar[data-collapsed="true"]) .sb-burger { left: 3.875rem; }

      #separator-sidebar[data-collapsed="true"] { width: 5rem; }
      #separator-sidebar[data-collapsed="true"] .sb-label { max-width: 0; opacity: 0; }
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
        collapsed: localStorage.getItem('adminSidebarCollapsed') === 'true',
        mobileOpen: false,
        isDesktop: window.innerWidth >= 640,
        academicOpen: {{ $academicActive ? 'true' : 'false' }},
        get isOpen() { return this.isDesktop ? !this.collapsed : this.mobileOpen },
        toggle() {
           if (this.isDesktop) {
              this.collapsed = !this.collapsed;
              localStorage.setItem('adminSidebarCollapsed', this.collapsed);
           } else {
              this.mobileOpen = !this.mobileOpen;
           }
        },
        openAcademic() {
           if (this.isDesktop && this.collapsed) {
              this.collapsed = false;
              localStorage.setItem('adminSidebarCollapsed', false);
              this.academicOpen = true;
           } else {
              this.academicOpen = !this.academicOpen;
           }
        }
     }"
     @resize.window.debounce.100ms="isDesktop = window.innerWidth >= 640; if (isDesktop) mobileOpen = false"
     @keydown.escape.window="mobileOpen = false">

   {{-- Mobile backdrop --}}
   <div x-show="mobileOpen" x-cloak
        x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm sm:hidden"></div>

   {{-- Admin Sidebar Component --}}
   <aside id="separator-sidebar"
          :data-collapsed="collapsed ? 'true' : null"
          class="fixed top-0 left-0 z-40 h-screen w-64 -translate-x-full sm:translate-x-0 transition-[width,transform] duration-300 ease-in-out"
          :class="{ '-translate-x-full': !mobileOpen, 'translate-x-0 shadow-2xl': mobileOpen }"
          aria-label="Sidebar">
      <div class="flex h-full flex-col border-e border-blue-100/80 bg-linear-to-b from-sky-50 via-blue-50 to-indigo-100 dark:border-slate-800 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950">

         {{-- Brand --}}
         <div class="sb-center flex h-18 shrink-0 items-center border-b border-blue-100/70 px-4 dark:border-slate-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center">
               <img src="{{ asset('images/logo.png') }}" alt="LMS Admin" class="sb-hide h-12 w-auto shrink-0">
               <img src="{{ asset('images/Icon.png') }}" alt="LMS Admin" class="sb-show h-10 w-auto shrink-0">
            </a>
         </div>

         {{-- Navigation --}}
         <nav class="sb-nav flex-1 overflow-y-auto overflow-x-hidden px-3 py-4">
            <p class="sb-heading mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 transition-all duration-300 dark:text-slate-500">Main Menu</p>

            <ul class="space-y-1">
               <li>
                  <a href="{{ route('admin.dashboard') }}" class="{{ $linkBase }} {{ request()->routeIs('admin.dashboard') ? $linkActive : $linkIdle }}">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/></svg>
                     <span class="sb-label">Dashboard</span>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Dashboard</span>
                  </a>
               </li>

               {{-- User Management --}}
               <li>
                  <a href="{{ route('admin.users.index') }}" class="{{ $linkBase }} {{ request()->routeIs('admin.users.*') ? $linkActive : $linkIdle }}">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                     <span class="sb-label">User Management</span>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">User Management</span>
                  </a>
               </li>

               {{-- Academic & Curriculum Dropdown --}}
               <li>
                  <button type="button" @click="openAcademic()" :aria-expanded="academicOpen" aria-controls="dropdown-academic"
                          class="{{ $linkBase }} w-full {{ $academicActive ? 'text-blue-700 dark:text-white' : $linkIdle }} hover:bg-white/80 dark:hover:bg-white/5">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                     <span class="sb-label flex-1 text-left">Academic</span>
                     <svg class="sb-hide h-4 w-4 shrink-0 transition-transform duration-300" :class="academicOpen && 'rotate-180'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Academic</span>
                  </button>
                  <div id="dropdown-academic" class="sb-hide grid transition-[grid-template-rows] duration-300 ease-in-out {{ $academicActive ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}"
                       :class="academicOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                     <ul class="relative space-y-0.5 overflow-hidden before:absolute before:left-[1.53rem] before:top-3 before:bottom-3 before:w-px before:bg-blue-200/70 dark:before:bg-slate-700">
                        <li class="pt-1">
                           <a href="{{ route('admin.curriculum.index') }}" class="{{ $subLink }} {{ $isActive('admin/curriculum*') ? $subActive : $subIdle }}">Curriculum & Subjects</a>
                        </li>
                        <li>
                           <!-- <a href="{{ url('/admin/school-year') }}" class="{{ $subLink }} {{ $isActive('admin/school-year*') ? $subActive : $subIdle }}">School Year & Terms</a> -->
                        </li>
                        <li>
                           <a href="{{ route('admin.enrollment.index') }}" class="{{ $subLink }} {{ $isActive('admin/enrollment*') ? $subActive : $subIdle }}">Enrollment</a>
                        </li>
                     </ul>
                  </div>
               </li>

               <li>
                  <a href="{{ route('admin.reports.index') }}" class="{{ $linkBase }} {{ request()->routeIs('admin.reports.*') ? $linkActive : $linkIdle }}">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18M7 16l4-4 4 4 5-6m-5 0h5v5"/></svg>
                     <span class="sb-label">Reports & Analytics</span>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Reports & Analytics</span>
                  </a>
               </li>

               <li>
                  <a href="{{ route('admin.settings') }}" class="{{ $linkBase }} {{ $isActive('admin/settings*') ? $linkActive : $linkIdle }}">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                     <span class="sb-label">Settings</span>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Settings</span>
                  </a>
               </li>
            </ul>

            <p class="sb-heading mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 transition-all duration-300 dark:text-slate-500">Help</p>
            <div class="sb-divider mx-3 my-4 hidden border-t border-blue-100 dark:border-slate-800"></div>

            <ul class="space-y-1">
               <li>
                  <a href="{{ route('admin.documentation') }}" class="{{ $linkBase }} {{ request()->routeIs('admin.documentation') ? $linkActive : $linkIdle }}">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13H7a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h12M9 3v14m7 0v4"/></svg>
                     <span class="sb-label">Documentation</span>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Documentation</span>
                  </a>
               </li>
               <li>
                  <a href="{{ route('admin.support') }}" class="{{ $linkBase }} {{ request()->routeIs('admin.support') ? $linkActive : $linkIdle }}">
                     <svg class="{{ $iconBase }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                     <span class="sb-label">Support</span>
                     <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Support</span>
                  </a>
               </li>
            </ul>
         </nav>

         {{-- Footer: profile, theme toggle, logout --}}
         <div class="shrink-0 space-y-2 border-t border-blue-100/80 bg-white/40 p-3 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-950/30">
            {{-- Profile card --}}
            <div class="sb-center flex items-center gap-3 rounded-xl bg-white/60 px-2.5 py-2 ring-1 ring-blue-100/80 dark:bg-white/5 dark:ring-white/10">
               <span class="relative shrink-0">
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-linear-to-br from-sky-400 to-indigo-600 text-xs font-semibold text-white shadow-sm ring-2 ring-white dark:ring-slate-800">{{ $initials ?: 'A' }}</span>
                  <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900" aria-hidden="true"></span>
               </span>
               <span class="sb-label flex min-w-0 flex-1 flex-col leading-tight">
                  <span class="truncate text-sm font-semibold text-slate-800 dark:text-white">{{ $userName }}</span>
                  <span class="truncate text-xs text-slate-500 dark:text-slate-400">Administrator</span>
               </span>
            </div>

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
                  <svg class="absolute inset-1.5 h-5 w-5 text-indigo-600 transition-all duration-500" :class="dark ? 'rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 0 1 .162.819A8.97 8.97 0 0 0 9 6a9 9 0 0 0 9 9 8.97 8.97 0 0 0 3.463-.69.75.75 0 0 1 .981.98 10.503 10.503 0 0 1-9.694 6.46c-5.799 0-10.5-4.7-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 0 1 .818.162Z" clip-rule="evenodd"/></svg>
                  <svg class="absolute inset-1.5 h-5 w-5 text-amber-300 transition-all duration-500" :class="dark ? 'rotate-0 scale-100 opacity-100' : '-rotate-90 scale-0 opacity-0'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z"/></svg>
               </span>
               <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700" x-text="dark ? 'Light mode' : 'Dark mode'"></span>
            </button>

            <form method="POST" action="{{ route('logout') }}">
               @csrf
               <button type="submit" class="{{ $linkBase }} w-full text-red-600 hover:bg-red-50 hover:text-red-700 hover:shadow-sm dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                  <svg class="{{ $iconBase }} group-hover:translate-x-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2"/></svg>
                  <span class="sb-label">Logout</span>
                  <span class="sb-tip rounded-lg bg-slate-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-lg dark:bg-slate-700">Logout</span>
               </button>
            </form>
         </div>
      </div>
   </aside>

   {{-- Apply saved collapsed state before first paint to avoid a width flash --}}
   <script>
      try {
         if (window.innerWidth >= 640 && localStorage.getItem('adminSidebarCollapsed') === 'true') {
            document.getElementById('separator-sidebar').setAttribute('data-collapsed', 'true');
         }
      } catch (e) {}
   </script>

   {{-- Animated burger toggle, pinned to the sidebar edge --}}
   <button type="button" @click="toggle()" x-cloak
           :aria-expanded="isOpen" aria-controls="separator-sidebar"
           :aria-label="isOpen ? 'Collapse sidebar' : 'Expand sidebar'"
           class="sb-burger fixed top-5 z-50 flex h-9 w-9 items-center justify-center rounded-full border border-blue-100 bg-white/90 text-slate-600 shadow-md backdrop-blur transition-all duration-300 ease-in-out hover:scale-110 hover:text-blue-600 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 dark:border-slate-700 dark:bg-slate-800/90 dark:text-slate-300 dark:hover:text-white"
           :class="mobileOpen ? 'left-[14.875rem]' : 'left-4'">
      <span class="relative block h-4 w-4">
         <span class="absolute left-0 top-1/2 h-0.5 w-4 -mt-px rounded-full bg-current transition-transform duration-300 ease-in-out" :class="isOpen ? 'rotate-45' : '-translate-y-1.5'"></span>
         <span class="absolute left-0 top-1/2 h-0.5 w-4 -mt-px rounded-full bg-current transition-all duration-200 ease-in-out" :class="isOpen ? 'scale-x-0 opacity-0' : 'scale-x-100 opacity-100'"></span>
         <span class="absolute left-0 top-1/2 h-0.5 w-4 -mt-px rounded-full bg-current transition-transform duration-300 ease-in-out" :class="isOpen ? '-rotate-45' : 'translate-y-1.5'"></span>
      </span>
   </button>
</div>
