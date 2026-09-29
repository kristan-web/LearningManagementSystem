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
    @include('partials.navigation.admin')
@endsection

@section('title', 'Admin Dashboard')

{{-- Landing/login theme: Plus Jakarta Sans, navy ink text, light brand-tinted background --}}
@section('body_class', 'font-jakarta !text-ink !bg-[#f4f6fb] dark:!bg-gray-900 dark:!text-gray-100')

@section('styles')
    {{-- Admin-specific styles --}}
@endsection
