{{--
    Layout: Student
    Purpose: Student-specific layout extending the main app layout.
    Includes student-specific sidebar navigation and learning views.
--}}
@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar.student')
@endsection

@section('navbar')
    @include('partials.styles.portal-theme')
    @include('partials.scripts.confirm-alerts')
    @include('partials.navigation.portal')
@endsection

@section('title', 'Student Dashboard')

{{-- Favicon: same logo as the login page and sidebar --}}
@push('head')
    <link rel="icon" type="image/png" href="{{ asset('images/Enrollment logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Enrollment logo.png') }}">
@endpush

{{-- Same theme as the admin layout: Plus Jakarta Sans, navy ink text, light brand-tinted background --}}
@section('body_class', 'lms-portal font-jakarta !text-ink !bg-[#f4f6fb] dark:!bg-gray-900 dark:!text-gray-100')
