{{-- Student: Support (content only; page body is partials.help.support) --}}
@extends('layouts.student')
@section('title', 'Support')

@php
    $channels = [
        ['title' => 'Registrar', 'value' => 'registrar@school.edu', 'href' => 'mailto:registrar@school.edu', 'note' => 'Enrollment, records and grades',
         'icon' => 'M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z'],
        ['title' => 'IT Support', 'value' => 'support@school.edu', 'href' => 'mailto:support@school.edu', 'note' => 'Sign-in and portal problems',
         'icon' => 'M9.75 17 9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z'],
        ['title' => 'IT Office', 'value' => 'Admin Building, Room 104', 'href' => null, 'note' => 'Mon–Fri, 8:00 AM – 5:00 PM',
         'icon' => 'M17.657 16.657 13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0ZM15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    ];

    $categories = ['Account & Sign-in', 'Assignments', 'Quizzes', 'Learning Materials', 'Grades', 'Enrollment', 'Bug Report', 'Other'];

    $tips = [
        'Ask your subject teacher first about scores and deadlines.',
        'Include the page name and any error message you saw.',
        'Never share your password with anyone.',
    ];
@endphp

@section('content')
    @include('partials.help.support', ['docsRoute' => 'student.documentation', 'supportRoute' => 'student.support', 'portalName' => 'student portal'])
@endsection
