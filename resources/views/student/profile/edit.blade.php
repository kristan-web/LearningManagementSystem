{{-- Student: My Profile. Body is partials.profile.page; posts to profile.update (identity fields are locked). --}}
@extends('layouts.student')
@section('title', 'My Profile')

@php
    $idIcon = 'M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 1 1 4 0v1m-4 0a2 2 0 1 0 4 0m-5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 0 0-2.83 2M15 11h3m-3 4h2';
@endphp

@section('content')
    @include('partials.profile.page', [
        'roleLabel' => 'Student',
        'facts' => [
            ['label' => 'Student No.', 'value' => $student?->student_number, 'icon' => $idIcon],
            ['label' => 'LRN', 'value' => $student?->lrn,
             'icon' => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14'],
            ['label' => 'Grade Level', 'value' => $student?->grade_level ? 'Grade ' . $student->grade_level : null,
             'icon' => 'M12 14l9-5-9-5-9 5 9 5Zm0 0 6.16-3.422a12.083 12.083 0 0 1 .665 6.479A11.952 11.952 0 0 0 12 20.055a11.952 11.952 0 0 0-6.824-2.998 12.078 12.078 0 0 1 .665-6.479L12 14Z'],
            ['label' => 'Strand', 'value' => $student?->strand?->strand_name,
             'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
        ],
        'record' => [
            ['label' => 'Full name', 'value' => trim($user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name)],
            ['label' => 'Email', 'value' => $user->email],
            ['label' => 'Section', 'value' => $student?->activeEnrollment?->section?->section_name],
            ['label' => 'Account created', 'value' => $user->created_at?->format('M d, Y')],
        ],
        'showSpecialization' => false,
        'specialization' => null,
        'cancelRoute' => 'student.dashboard',
        'supportRoute' => 'student.support',
        'passwordToggle' => false,
    ])
@endsection
