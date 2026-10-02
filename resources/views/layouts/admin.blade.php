{{--
    Layout: Admin
    Purpose: Admin-specific layout extending the main app layout.
    Includes admin-specific sidebar navigation and dashboard widgets.
--}}
@extends('layouts.app')

@section('sidebar')
    @include('partials.sidebar.admin')
@endsection

@section('navbar')
    @include('partials.scripts.confirm-alerts')
    @include('partials.navigation.admin')
@endsection

@section('title', 'Admin Dashboard')

{{-- Favicon: same logo as the login page and sidebar --}}
@push('head')
    <link rel="icon" type="image/png" href="{{ asset('images/Enrollment logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Enrollment logo.png') }}">
@endpush

{{-- Landing/login theme: Plus Jakarta Sans, navy ink text, light brand-tinted background --}}
@section('body_class', 'font-jakarta !text-ink !bg-[#f4f6fb] dark:!bg-gray-900 dark:!text-gray-100')

@section('styles')
    {{-- Admin-specific styles --}}
@endsection
