{{-- Shared quiz create/edit form. Expects: $action, $method, $quiz (nullable), $submitLabel --}}
@php
    $label = 'mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-3 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';

    $classLabel = fn ($schedule) => trim(
        ($schedule->subject?->subject_name ?? '') . ' — ' . ($schedule->section?->section_name ?? '')
    );

    $dueValue = $quiz?->due_date ? $quiz->due_date->format('Y-m-d') . 'T' . $quiz->due_date->format('H:i') : '';

    // Seed the Alpine question list: old() input on a validation bounce, else the quiz's
    // existing questions on edit, else a single blank question to start from.
    $seedQuestions = old('questions') ?? ($quiz?->questions->map(fn ($q) => [
        'question_text' => $q->question_text,
        'question_type' => $q->question_type,
        'options' => $q->options ?? [''],
        'correct_answer' => $q->correct_answer,
    ])->all() ?? []);

    if (empty($seedQuestions)) {
        $seedQuestions = [[
            'question_text' => '', 'question_type' => 'multiple_choice', 'options' => ['', ''], 'correct_answer' => '',
        ]];
    }
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data"
      class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"
      x-data="quizForm({{ json_encode(array_values($seedQuestions)) }})">
    @csrf
    @if ($method === 'PUT')
        @method('PUT')
    @endif

    <div class="space-y-5">
        <div>
            <label for="schedule_id" class="{{ $label }}">Class</label>
            <select name="schedule_id" id="schedule_id" required class="{{ $input }}">
                <option value="">Select a class…</option>
                @foreach ($schedules as $schedule)
                    <option value="{{ $schedule->schedule_id }}" {{ (old('schedule_id', $quiz?->schedule_id)) == $schedule->schedule_id ? 'selected' : '' }}>
                        {{ $classLabel($schedule) }}
                    </option>
                @endforeach
            </select>
            @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="title" class="{{ $label }}">Title</label>
            <input type="text" name="title" id="title" required maxlength="255" class="{{ $input }}"
                   value="{{ old('title', $quiz?->title) }}" placeholder="e.g. Unit 1 Quiz">
            @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="due_date" class="{{ $label }}">Due date &amp; time <span class="text-xs text-slate-400">(optional)</span></label>
                <input type="datetime-local" name="due_date" id="due_date" class="{{ $input }}" value="{{ old('due_date', $dueValue) }}">
                @error('due_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="time_limit_minutes" class="{{ $label }}">Time limit (minutes) <span class="text-xs text-slate-400">(optional)</span></label>
                <input type="number" name="time_limit_minutes" id="time_limit_minutes" min="1" max="600" class="{{ $input }}"
                       value="{{ old('time_limit_minutes', $quiz?->time_limit_minutes) }}">
                @error('time_limit_minutes') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-700">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">Questions</h2>
            <button type="button" @click="addQuestion()"
                    class="rounded-lg border border-blue-200 bg-blue-500/10 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-500 hover:text-white dark:border-blue-500/30 dark:text-blue-300">
                + Add question
            </button>
        </div>
        @error('questions') <p class="{{ $error }}">{{ $message }}</p> @enderror

        <template x-for="(question, qIndex) in questions" :key="qIndex">
            <div class="mt-4 rounded-xl border border-slate-200 p-4 dark:border-slate-600">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 space-y-3">
                        <div>
                            <label class="{{ $label }}">Question text</label>
                            <input type="text" :name="`questions[${qIndex}][question_text]`" x-model="question.question_text" required
                                   class="{{ $input }}" placeholder="Type the question…">
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="{{ $label }}">Type</label>
                                <select :name="`questions[${qIndex}][question_type]`" x-model="question.question_type"
                                        @change="onTypeChange(question)" class="{{ $input }}">
                                    <option value="multiple_choice">Multiple choice</option>
                                    <option value="true_false">True / False</option>
                                    <option value="short_answer">Short answer</option>
                                </select>
                            </div>
                        </div>

                        {{-- Multiple choice: editable option list --}}
                        <div x-show="question.question_type === 'multiple_choice'" class="space-y-2">
                            <label class="{{ $label }}">Options</label>
                            <template x-for="(option, oIndex) in question.options" :key="oIndex">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="`questions[${qIndex}][options][${oIndex}]`" x-model="question.options[oIndex]"
                                           class="{{ $input }}" placeholder="Option text">
                                    <button type="button" @click="removeOption(question, oIndex)"
                                            class="rounded-lg border border-red-200 px-2 py-2 text-xs text-red-600 hover:bg-red-500 hover:text-white dark:border-red-500/30 dark:text-red-400">&times;</button>
                                </div>
                            </template>
                            <button type="button" @click="addOption(question)"
                                    class="text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400">+ Add option</button>
                        </div>

                        <div>
                            <label class="{{ $label }}">
                                Correct answer
                                <span class="text-xs text-slate-400" x-show="question.question_type === 'true_false'">(True or False)</span>
                            </label>
                            <template x-if="question.question_type === 'true_false'">
                                <select :name="`questions[${qIndex}][correct_answer]`" x-model="question.correct_answer" class="{{ $input }}">
                                    <option value="True">True</option>
                                    <option value="False">False</option>
                                </select>
                            </template>
                            <template x-if="question.question_type !== 'true_false'">
                                <input type="text" :name="`questions[${qIndex}][correct_answer]`" x-model="question.correct_answer" required
                                       class="{{ $input }}" placeholder="Exact correct answer">
                            </template>
                        </div>
                    </div>

                    <button type="button" @click="removeQuestion(qIndex)"
                            class="rounded-lg border border-red-200 bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/30 dark:text-red-400">
                        Remove
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('teacher.quizzes.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</a>
        <button type="submit" class="rounded-lg bg-linear-to-r from-blue-500 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:shadow-blue-500/40">{{ $submitLabel }}</button>
    </div>
</form>

@section('scripts')
<script>
    function quizForm(seedQuestions) {
        return {
            questions: seedQuestions,
            addQuestion() {
                this.questions.push({ question_text: '', question_type: 'multiple_choice', options: ['', ''], correct_answer: '' });
            },
            removeQuestion(index) {
                if (this.questions.length > 1) {
                    this.questions.splice(index, 1);
                }
            },
            addOption(question) {
                question.options.push('');
            },
            removeOption(question, index) {
                if (question.options.length > 1) {
                    question.options.splice(index, 1);
                }
            },
            onTypeChange(question) {
                if (question.question_type === 'true_false') {
                    question.correct_answer = 'True';
                } else if (question.question_type === 'short_answer') {
                    question.correct_answer = '';
                }
            },
        };
    }
</script>
@endsection
