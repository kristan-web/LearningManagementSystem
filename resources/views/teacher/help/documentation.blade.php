{{-- Teacher: Documentation (content only; page body is partials.help.documentation) --}}
@extends('layouts.teacher')
@section('title', 'Documentation')

@php
    // Each section: id, title, summary, icon path, link, steps, tips.
    $sections = [
        [
            'id' => 'getting-started',
            'title' => 'Getting Started',
            'summary' => 'Find your way around the teacher portal.',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7Z',
            'route' => route('teacher.dashboard'),
            'steps' => [
                'Sign in with the teacher account given by the school.',
                'Use the sidebar to move between pages. Click the round button on its edge to collapse it, or tap it on a phone to open the menu.',
                'Switch between light and dark mode with the Light / Dark switch at the bottom of the sidebar.',
                'The Dashboard shows today\'s classes, your grading queue, upcoming events and the latest announcements.',
            ],
            'tips' => ['Use "Take Attendance" on the Dashboard banner to jump straight to today\'s roster.'],
        ],
        [
            'id' => 'classes',
            'title' => 'My Classes & Schedule',
            'summary' => 'See every class you handle and when it meets.',
            'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
            'route' => route('teacher.classes.index'),
            'steps' => [
                'Open Classes & Teaching → My Classes.',
                'Each card shows the subject, section, time, room and number of students.',
                'Use the card buttons to open that class\'s assignments, quizzes, materials, gradebook or attendance.',
                'Open Teaching Schedule for the full weekly timetable. Today\'s classes are highlighted.',
            ],
            'tips' => ['Classes come from the class schedule set by the admin. Ask the admin if one is missing.'],
        ],
        [
            'id' => 'assignments',
            'title' => 'Assignments & Grading',
            'summary' => 'Post assignments and grade submissions.',
            'icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4',
            'route' => route('teacher.assignments.index'),
            'steps' => [
                'Open Assignments and click New Assignment.',
                'Pick the class, enter the title, due date, maximum score and instructions, then post it.',
                'Click Grade on an assignment to see every student and their submission.',
                'Download the file, enter a score and click Grade.',
            ],
            'tips' => ['A grade is final once saved, so check the score before you confirm.'],
        ],
        [
            'id' => 'quizzes',
            'title' => 'Quizzes',
            'summary' => 'Create quizzes from a CSV file.',
            'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'route' => route('teacher.quizzes.index'),
            'steps' => [
                'Open Quizzes and click New Quiz.',
                'Download the CSV template and fill in one question per row.',
                'Pick the class, set the title, timer, attempts and due date, then upload the CSV.',
                'Quizzes are scored automatically. Click Scores to see results in the gradebook.',
            ],
            'tips' => ['If the CSV has an error, nothing is saved. Fix the row named in the message and upload again.'],
        ],
        [
            'id' => 'materials',
            'title' => 'Learning Materials',
            'summary' => 'Share modules, slides and videos.',
            'icon' => 'M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M17 8l-5-5-5 5M12 3v12',
            'route' => route('teacher.materials.index'),
            'steps' => [
                'Open Learning Materials and click Upload Material.',
                'Pick the class, add a title, choose a status and select the file (max 20 MB).',
                'Set the status to Published when students should see it.',
                'Use the row buttons to preview, download, edit or delete a material.',
            ],
            'tips' => ['Draft and Archived materials stay hidden from students.'],
        ],
        [
            'id' => 'attendance',
            'title' => 'Attendance',
            'summary' => 'Record who came to class.',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'route' => route('teacher.attendance.index'),
            'steps' => [
                'Open Attendance, pick the class and the date, then click Open roster.',
                'Everyone starts as Present. Change only the students who were Late, Absent or Excused.',
                'Click Save attendance. You can open the same date again later to correct it.',
                'Recent sessions on the right show the counts for past dates.',
            ],
            'tips' => ['Students see the attendance you record on their own Attendance page.'],
        ],
        [
            'id' => 'grades',
            'title' => 'Grades & Records',
            'summary' => 'Review every score in a class.',
            'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z',
            'route' => route('teacher.grades.index'),
            'steps' => [
                'Open Grades & Records and pick a class.',
                'Each row is a student; each column is an assignment or quiz.',
                'Quizzes use the student\'s best attempt. "To grade" means a submission still needs a score.',
                'The Average column is the mean percentage of everything the student has been scored on.',
            ],
            'tips' => ['Averages below 75% are shown in red.'],
        ],
        [
            'id' => 'communication',
            'title' => 'Communication',
            'summary' => 'Message students and post announcements.',
            'icon' => 'M4 13h3.439a.991.991 0 0 1 .908.6 3.978 3.978 0 0 0 7.306 0 .99.99 0 0 1 .908-.6H20M4 13v6a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-6M4 13l2-9h12l2 9M9 7h6m-7 3h8',
            'route' => route('teacher.communication.index'),
            'steps' => [
                'Open Communication and pick a student under "New message to…".',
                'Type your message and click Send. Replies appear in the same conversation.',
                'The envelope at the top right shows how many messages are unread.',
                'For notices to a whole class, use Announcements instead.',
            ],
            'tips' => ['You can message students enrolled in the sections you teach.'],
        ],
    ];

    $faqs = [
        ['q' => 'A class is missing from My Classes. What do I do?', 'a' => 'Classes come from the admin\'s class schedule. Ask the admin to add you to that schedule.'],
        ['q' => 'Can I change a grade after saving it?', 'a' => 'Not yet from the grading page. Contact the admin if a saved grade is wrong.'],
        ['q' => 'A student says they cannot see my material.', 'a' => 'Check that the material\'s status is Published and that it was uploaded to the right class.'],
        ['q' => 'My teacher number is wrong or "Not assigned". Who can fix it?', 'a' => 'The school administrator. Send a request from the Support page.'],
    ];
@endphp

@section('content')
    @include('partials.help.documentation', ['supportRoute' => 'teacher.support', 'portalName' => 'teacher portal', 'roleWord' => 'teacher'])
@endsection
