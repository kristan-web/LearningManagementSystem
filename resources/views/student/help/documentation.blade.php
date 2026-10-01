{{-- Student: Documentation (content only; page body is partials.help.documentation) --}}
@extends('layouts.student')
@section('title', 'Documentation')

@php
    // Each section: id, title, summary, icon path, link, steps, tips.
    $sections = [
        [
            'id' => 'getting-started',
            'title' => 'Getting Started',
            'summary' => 'Find your way around the student portal.',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7Z',
            'route' => route('student.dashboard'),
            'steps' => [
                'Sign in with the student account given by your school.',
                'Use the sidebar to move between pages. On a phone, tap the menu button at the top left to open it.',
                'Switch between light and dark mode with the Light / Dark switch at the bottom of the sidebar.',
                'The Dashboard shows your pending assignments, quizzes, upcoming events and the latest announcements.',
            ],
            'tips' => ['Check the Dashboard every day so you do not miss a deadline.'],
        ],
        [
            'id' => 'assignments',
            'title' => 'Assignments',
            'summary' => 'Read instructions and submit your work.',
            'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4',
            'route' => route('student.assignments.index'),
            'steps' => [
                'Open Academics → Assignments.',
                'Click an assignment title to read the full instructions and join the discussion.',
                'Choose your file (PDF, Word, PowerPoint, Excel or ZIP) and click Submit.',
                'Your status changes to Submitted, or Late if the due date has passed. Your score appears once your teacher grades it.',
            ],
            'tips' => ['You can submit only once, so check your file before you click Submit.'],
        ],
        [
            'id' => 'quizzes',
            'title' => 'Quizzes',
            'summary' => 'Take quizzes and review your results.',
            'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'route' => route('student.quizzes.index'),
            'steps' => [
                'Open Academics → Quizzes and click a subject to see its quizzes.',
                'Click Start to begin, or Resume to continue an attempt you already started.',
                'Answer every question, then click Submit Quiz.',
                'Click Review after your last attempt to see the correct answers.',
            ],
            'tips' => ['Timed quizzes submit themselves when the timer reaches zero.'],
        ],
        [
            'id' => 'materials',
            'title' => 'Learning Materials',
            'summary' => 'Preview and download study resources.',
            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
            'route' => route('student.materials.index'),
            'steps' => [
                'Open Academics → Materials and pick a subject.',
                'Click Preview to open a file in a new tab, or Download to save it.',
            ],
            'tips' => ['New materials appear as soon as your teacher publishes them.'],
        ],
        [
            'id' => 'calendar',
            'title' => 'Calendar & Schedule',
            'summary' => 'See your classes, deadlines and personal events.',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z',
            'route' => route('calendar.index'),
            'steps' => [
                'Open Academics → Schedule to see your weekly class timetable.',
                'Open Calendar to see assignment and quiz due dates.',
                'Click Add Event, or click a day, to add a personal event. Click your own event to edit or delete it.',
            ],
            'tips' => ['Only your personal events can be edited. Deadlines come from your teachers.'],
        ],
        [
            'id' => 'profile',
            'title' => 'My Profile',
            'summary' => 'Update your contact details and password.',
            'icon' => 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z',
            'route' => route('profile.edit'),
            'steps' => [
                'Open My Profile from the sidebar, or click your name at the top right.',
                'Update your birthdate, gender, contact number and address.',
                'Enter a new password only if you want to change it. Leave it blank to keep the current one.',
                'Click Save Changes.',
            ],
            'tips' => ['Your name, email, LRN and student number can only be changed by the school.'],
        ],
    ];

    $faqs = [
        ['q' => 'I forgot my password. What do I do?', 'a' => 'Use "Forgot password" on the sign-in page, or ask your adviser or the registrar to reset it.'],
        ['q' => 'I submitted the wrong file. Can I change it?', 'a' => 'Not yet. Message your teacher and explain the problem.'],
        ['q' => 'Why can I not see a subject or its materials?', 'a' => 'You only see subjects from your enrolled section. If one is missing, contact the registrar.'],
        ['q' => 'My name or LRN is wrong. Who can fix it?', 'a' => 'The registrar or the school administrator. Send a request from the Support page.'],
    ];
@endphp

@section('content')
    @include('partials.help.documentation', ['supportRoute' => 'student.support', 'portalName' => 'student portal', 'roleWord' => 'student'])
@endsection
