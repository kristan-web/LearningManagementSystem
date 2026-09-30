@php
   $user = auth()->user();
   $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Student';
   $initials = collect(explode(' ', trim($userName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp

{{-- Button to toggle sidebar on mobile devices --}}
<button data-drawer-target="separator-sidebar" data-drawer-toggle="separator-sidebar" aria-controls="separator-sidebar" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
   <span class="sr-only">Open sidebar</span>
   <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
      <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h10"/>
   </svg>
</button>

{{-- Sidebar Component --}}
<aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
   <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50 dark:bg-gray-800 border-e border-gray-200 dark:border-gray-700 flex flex-col">
      <a href="{{ route('student.dashboard') }}" class="flex items-center ps-2.5 mb-5 space-x-3 rtl:space-x-reverse">
         <span class="self-center text-xl font-semibold whitespace-nowrap text-gray-900 dark:text-white">LMS</span>
         <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Student</span>
      </a>

      <ul class="space-y-2 font-medium">
         <li>
            <a href="{{ route('student.dashboard') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z"/></svg>
               <span class="ms-3">Dashboard</span>
            </a>
<li>
             <a href="{{ route('announcements.index') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5.365V3m0 2.365c3.37 0 6.5 2.78 6.5 7.135v3.528l.89.89a.75.75 0 0 1-.53 1.282H5.14a.75.75 0 0 1-.53-1.282l.89-.89V12.5c0-4.355 3.13-7.135 6.5-7.135Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/></svg>
                <span class="ms-3">Announcements</span>
             </a>
          </li>
         </li>
         <li>
            <button type="button" class="flex items-center w-full justify-between px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group" aria-controls="dropdown-academics" data-collapse-toggle="dropdown-academics">
                  <span class="flex items-center">
                     <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                     <span class="ms-3 text-left whitespace-nowrap">Academics</span>
                  </span>
                  <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
            </button>
            <ul id="dropdown-academics" class="hidden py-2 space-y-2">
                  <li>
                     <a href="{{ url('/student/schedule') }}" class="pl-10 flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">Schedule</a>
                  </li>
                  <li>
                     <a href="{{ route('student.assignments.index') }}" class="pl-10 flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">Assignments</a>
                  </li>
                  <li>
                     <a href="{{ route('student.quizzes.index') }}" class="pl-10 flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">Quizzes</a>
                  </li>
                  <li>
                     <a href="{{ url('/student/materials') }}" class="pl-10 flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">Materials</a>
                  </li>
                  <li>
                     <a href="{{ url('/student/grades') }}" class="pl-10 flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">Grades</a>
                  </li>
            </ul>
         </li>
         <li>
            <a href="{{ route('calendar.index') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Calendar</span>
            </a>
         </li>
         <li>
            <a href="{{ route('announcements.index') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 5.5a2.5 2.5 0 0 1 5 0V9m-5-3.5v3.379M9.5 5.5A2.5 2.5 0 0 0 7 8v6.5c0 1.243-.895 2.235-1.72 2.928A1 1 0 0 0 6 19h12a1 1 0 0 0 .72-1.572c-.825-.693-1.72-1.685-1.72-2.928V8a2.5 2.5 0 0 0-2.5-2.5m-5 0h5M10 19v1a2 2 0 1 0 4 0v-1"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Announcements</span>
            </a>
         </li>
         <li>
            <a href="{{ url('/student/enrollment') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v14M9 5v14M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Enrollment</span>
               <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Active</span>
            </a>
         </li>
         <li>
            <a href="{{ url('/student/attendance') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Attendance</span>
            </a>
         </li>
         <li>
            <a href="{{ url('/student/communication') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h3.439a.991.991 0 0 1 .908.6 3.978 3.978 0 0 0 7.306 0 .99.99 0 0 1 .908-.6H20M4 13v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6M4 13l2-9h12l2 9M9 7h6m-7 3h8"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Inbox</span>
               <span class="inline-flex items-center justify-center w-5 h-5 ms-2 text-xs font-medium text-red-800 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">2</span>
            </a>
         </li>
         <li>
            <a href="{{ route('profile.edit') }}" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2.236-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">My Profile</span>
            </a>
         </li>
      </ul>

      <ul class="space-y-2 font-medium border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
         <li>
            <a href="#" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13H7a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h12M9 3v14m7 0v4"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Documentation</span>
            </a>
         </li>
         <li>
            <a href="#" class="flex items-center px-2 py-1.5 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
               <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
               <span class="flex-1 ms-3 whitespace-nowrap">Support</span>
            </a>
         </li>
         <li>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
               @csrf
               <button type="submit" class="flex items-center w-full px-2 py-1.5 text-red-600 rounded-lg dark:text-red-400 hover:bg-red-50 dark:hover:bg-gray-700 group text-left">
                  <svg class="shrink-0 w-5 h-5 transition duration-75" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H4m12 0-4 4m4-4-4-4m3-4h2a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-2"/></svg>
                  <span class="flex-1 ms-3 whitespace-nowrap">Sign Out</span>
               </button>
            </form>
         </li>
      </ul>

      {{-- Account footer: avatar, name, role badge, and dark/light mode toggle --}}
      <div class="mt-auto pt-4 border-t border-gray-200 dark:border-gray-700">
         <div class="flex items-center gap-3 px-2 py-1.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-semibold text-white">{{ $initials ?: 'S' }}</span>
            <div class="min-w-0 flex-1">
               <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $userName }}</p>
               <p class="truncate text-xs text-gray-500 dark:text-gray-400">Student</p>
            </div>
            <button type="button" @click="dark = !dark"
                    :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
                    class="group relative flex h-9 w-9 shrink-0 items-center justify-center overflow-visible rounded-full text-gray-500 transition-colors duration-200 hover:bg-gray-100 hover:text-blue-600 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-yellow-300">
               <svg class="absolute h-5 w-5 transition-all duration-500" :class="dark ? 'rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 0 1 8.646 3.646 9.003 9.003 0 0 0 12 21a9.003 9.003 0 0 0 8.354-5.646z"/></svg>
               <svg class="absolute h-5 w-5 transition-all duration-500" :class="dark ? 'rotate-0 scale-100 opacity-100' : '-rotate-90 scale-0 opacity-0'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364-.707-.707M6.343 6.343l-.707-.707m12.728 0-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0z"/></svg>
            </button>
         </div>
      </div>
   </div>
</aside>
