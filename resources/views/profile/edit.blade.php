{{-- Profile: Teacher self-service edit (students get student.profile.edit). Body is partials.profile.page; posts to profile.update. --}}
@extends('layouts.teacher')
@section('title', 'My Profile')

@php
    $teacherNumber = $teacher->teacher_number ?? null;
    $teacherNumber = str_starts_with((string) $teacherNumber, 'TCH-PENDING-') ? 'Not assigned' : $teacherNumber;
    $idIcon = 'M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 1 1 4 0v1m-4 0a2 2 0 1 0 4 0m-5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 0 0-2.83 2M15 11h3m-3 4h2';
@endphp

@section('content')
    @include('partials.profile.page', [
        'roleLabel' => 'Teacher',
        'facts' => [
            ['label' => 'Teacher No.', 'value' => $teacherNumber, 'icon' => $idIcon],
            ['label' => 'Specialization', 'value' => $teacher->specialization ?? null,
             'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
            ['label' => 'Birthday', 'value' => $user->birthdate?->format('M j, Y'),
             'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z'],
            ['label' => 'Member since', 'value' => $user->created_at?->format('M Y'),
             'icon' => 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ],
        'record' => [
            ['label' => 'Full name', 'value' => trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name)],
            ['label' => 'Email', 'value' => $user->email],
            ['label' => 'Teacher no.', 'value' => $teacherNumber],
            ['label' => 'Account created', 'value' => $user->created_at?->format('M d, Y')],
        ],
        'showSpecialization' => true,
        'specialization' => $teacher->specialization ?? null,
        'cancelRoute' => 'teacher.dashboard',
        'supportRoute' => 'teacher.support',
        'passwordToggle' => true,
    ])
@endsection
