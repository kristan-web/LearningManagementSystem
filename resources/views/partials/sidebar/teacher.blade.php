{{-- Teacher sidebar: links only; layout and behaviour live in partials.sidebar.portal --}}
@php
   $isActive = fn (...$patterns) => request()->is(...$patterns);
@endphp

@include('partials.sidebar.portal', [
   'roleLabel' => 'Teacher',
   'homeRoute' => 'teacher.dashboard',
   'group' => [
      'label' => 'Classes & Teaching',
      'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
      'links' => [
         ['label' => 'My Classes', 'href' => route('teacher.classes.index'), 'active' => $isActive('teacher/classes*')],
         ['label' => 'Teaching Schedule', 'href' => route('teacher.schedule.index'), 'active' => $isActive('teacher/schedule*')],
         ['label' => 'Assignments', 'href' => route('teacher.assignments.index'), 'active' => $isActive('teacher/assignments*', 'teacher/submissions*')],
         ['label' => 'Quizzes', 'href' => route('teacher.quizzes.index'), 'active' => $isActive('teacher/quizzes*')],
         ['label' => 'Learning Materials', 'href' => route('teacher.materials.index'), 'active' => $isActive('teacher/materials*')],
      ],
   ],
   'mainLinks' => [
      ['label' => 'Grades & Records', 'href' => route('teacher.grades.index'), 'active' => $isActive('teacher/grades*'),
       'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4'],
      ['label' => 'Attendance', 'href' => route('teacher.attendance.index'), 'active' => $isActive('teacher/attendance*'),
       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
      ['label' => 'Calendar', 'href' => route('calendar.index'), 'active' => $isActive('calendar*'),
       'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z'],
      ['label' => 'Announcements', 'href' => route('announcements.index'), 'active' => $isActive('announcements*'),
       'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9'],
      ['label' => 'Communication', 'href' => route('teacher.communication.index'), 'active' => $isActive('teacher/communication*'),
       'icon' => 'M4 13h3.439a.991.991 0 0 1 .908.6 3.978 3.978 0 0 0 7.306 0 .99.99 0 0 1 .908-.6H20M4 13v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6M4 13l2-9h12l2 9M9 7h6m-7 3h8'],
      ['label' => 'My Profile', 'href' => route('profile.edit'), 'active' => $isActive('profile*'),
       'icon' => 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z'],
   ],
   'helpLinks' => [
      ['label' => 'Documentation', 'href' => route('teacher.documentation'), 'active' => $isActive('teacher/documentation*'),
       'icon' => 'M5 19V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13H7a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h12M9 3v14m7 0v4'],
      ['label' => 'Support', 'href' => route('teacher.support'), 'active' => $isActive('teacher/support*'),
       'icon' => 'M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
   ],
])
