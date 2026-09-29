{{-- Teacher: Edit Quiz --}}
@extends('layouts.teacher')
@section('title', 'Edit Quiz')

@section('content')
<div class="mx-auto w-full max-w-3xl space-y-6 pt-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Quiz</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Update this quiz for your class.</p>
    </div>

    @include('teacher.quizzes._form', [
        'action' => route('teacher.quizzes.update', $quiz->quiz_id),
        'method' => 'PUT',
        'quiz' => $quiz,
        'submitLabel' => 'Save Changes',
    ])
</div>
@endsection
