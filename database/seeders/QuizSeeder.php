<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

/**
 * Placeholder quiz data so the student calendar (Module 9) has something to
 * render, and so quizzes can actually be taken end-to-end. Reuses the demo
 * schedule set up by AssignmentSeeder. Each quiz gets 5 simple
 * multiple_choice questions.
 *
 * Safe to re-run: uses firstOrCreate for quizzes and only seeds a quiz's
 * questions the first time it is created (skips if it already has any).
 */
class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $schedule = (new AssignmentSeeder())->demoSchedule();

        $dueDates = [
            now()->addDays(1),
            now()->addDays(6),
            now()->addDays(11),
            now()->addDays(16),
            now()->addDays(21),
        ];

        $quizzes = $this->quizData();

        foreach ($quizzes as $index => $quizData) {
            $quiz = Quiz::firstOrCreate(
                ['schedule_id' => $schedule->schedule_id, 'title' => $quizData['title']],
                [
                    'time_limit_minutes' => 20,
                    'attempts_allowed' => 2,
                    'due_date' => $dueDates[$index],
                ]
            );

            if ($quiz->questions()->exists()) {
                continue;
            }

            foreach ($quizData['questions'] as $question) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->quiz_id,
                    'question_text' => $question['text'],
                    'question_type' => 'multiple_choice',
                    'options' => $question['options'],
                    'correct_answer' => $question['answer'],
                ]);
            }
        }
    }

    /** @return array<int, array{title: string, questions: array<int, array{text: string, options: string[], answer: string}>}> */
    private function quizData(): array
    {
        return [
            [
                'title' => 'Quiz 1: Vocabulary Check',
                'questions' => [
                    ['text' => 'Which word means "happy"?', 'options' => ['Sad', 'Joyful', 'Angry', 'Tired'], 'answer' => 'Joyful'],
                    ['text' => 'Which word means "large"?', 'options' => ['Tiny', 'Small', 'Huge', 'Narrow'], 'answer' => 'Huge'],
                    ['text' => 'Which word is a synonym for "quick"?', 'options' => ['Slow', 'Fast', 'Lazy', 'Quiet'], 'answer' => 'Fast'],
                    ['text' => 'Which word means the opposite of "begin"?', 'options' => ['Start', 'Open', 'End', 'Continue'], 'answer' => 'End'],
                    ['text' => 'Which word means "to look at closely"?', 'options' => ['Ignore', 'Examine', 'Forget', 'Avoid'], 'answer' => 'Examine'],
                ],
            ],
            [
                'title' => 'Quiz 2: Chapter 3 Review',
                'questions' => [
                    ['text' => 'What is the powerhouse of the cell?', 'options' => ['Nucleus', 'Mitochondria', 'Ribosome', 'Cytoplasm'], 'answer' => 'Mitochondria'],
                    ['text' => 'How many continents are there on Earth?', 'options' => ['5', '6', '7', '8'], 'answer' => '7'],
                    ['text' => 'What gas do plants absorb from the air?', 'options' => ['Oxygen', 'Nitrogen', 'Carbon Dioxide', 'Hydrogen'], 'answer' => 'Carbon Dioxide'],
                    ['text' => 'Which planet is known as the Red Planet?', 'options' => ['Venus', 'Mars', 'Jupiter', 'Saturn'], 'answer' => 'Mars'],
                    ['text' => 'What is the largest ocean on Earth?', 'options' => ['Atlantic', 'Indian', 'Arctic', 'Pacific'], 'answer' => 'Pacific'],
                ],
            ],
            [
                'title' => 'Quiz 3: Formulas and Units',
                'questions' => [
                    ['text' => 'What is the SI unit of length?', 'options' => ['Gram', 'Meter', 'Second', 'Liter'], 'answer' => 'Meter'],
                    ['text' => 'What is the formula for area of a rectangle?', 'options' => ['length + width', 'length x width', 'length / width', '2(length + width)'], 'answer' => 'length x width'],
                    ['text' => 'What is the SI unit of mass?', 'options' => ['Meter', 'Kilogram', 'Second', 'Newton'], 'answer' => 'Kilogram'],
                    ['text' => 'What is the unit of electric current?', 'options' => ['Volt', 'Watt', 'Ampere', 'Ohm'], 'answer' => 'Ampere'],
                    ['text' => 'What is the formula for the area of a circle?', 'options' => ['2 x pi x r', 'pi x r^2', 'pi x d', 'r^2'], 'answer' => 'pi x r^2'],
                ],
            ],
            [
                'title' => 'Quiz 4: Midterm Practice',
                'questions' => [
                    ['text' => 'Who wrote "Romeo and Juliet"?', 'options' => ['Charles Dickens', 'William Shakespeare', 'Mark Twain', 'Jane Austen'], 'answer' => 'William Shakespeare'],
                    ['text' => 'What is 12 x 8?', 'options' => ['96', '86', '106', '90'], 'answer' => '96'],
                    ['text' => 'What is the capital of the Philippines?', 'options' => ['Cebu', 'Davao', 'Manila', 'Quezon City'], 'answer' => 'Manila'],
                    ['text' => 'Which of these is a prime number?', 'options' => ['9', '15', '17', '21'], 'answer' => '17'],
                    ['text' => 'What is the chemical symbol for water?', 'options' => ['O2', 'H2O', 'CO2', 'NaCl'], 'answer' => 'H2O'],
                ],
            ],
            [
                'title' => 'Quiz 5: General Knowledge',
                'questions' => [
                    ['text' => 'How many days are there in a leap year?', 'options' => ['364', '365', '366', '367'], 'answer' => '366'],
                    ['text' => 'What is the smallest prime number?', 'options' => ['0', '1', '2', '3'], 'answer' => '2'],
                    ['text' => 'Which organ pumps blood throughout the body?', 'options' => ['Lungs', 'Liver', 'Heart', 'Kidney'], 'answer' => 'Heart'],
                    ['text' => 'What is the freezing point of water in Celsius?', 'options' => ['0', '32', '100', '-1'], 'answer' => '0'],
                    ['text' => 'Which of these is a programming language?', 'options' => ['HTML', 'Python', 'CSS', 'JSON'], 'answer' => 'Python'],
                ],
            ],
        ];
    }
}
