<?php

namespace App\Services\Quiz;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Parses a teacher-uploaded CSV of quiz questions and imports them, all or
 * nothing, inside a transaction. Expected columns (header row required):
 *
 *   question_text, question_type, option_a, option_b, option_c, option_d, correct_answer
 *
 * - question_type: multiple_choice | true_false | short_answer
 * - multiple_choice: option_a..option_d required (at least 2), correct_answer
 *   must match one of the supplied options (case-insensitive).
 * - true_false: correct_answer must be "true" or "false" (case-insensitive).
 * - short_answer: option columns are ignored; correct_answer is the expected text.
 */
class QuizCsvImporter
{
    private const REQUIRED_COLUMNS = ['question_text', 'question_type', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
    private const VALID_TYPES = ['multiple_choice', 'true_false', 'short_answer'];

    /**
     * @throws RuntimeException with a newline-separated, row-numbered list of
     *         every validation error found (no partial import on failure).
     */
    public function import(Quiz $quiz, string $csvPath): int
    {
        $rows = $this->parse($csvPath);
        $errors = [];
        $questions = [];

        foreach ($rows as $lineNumber => $row) {
            $questionErrors = $this->validateRow($row);

            if ($questionErrors !== []) {
                foreach ($questionErrors as $error) {
                    $errors[] = "Row {$lineNumber}: {$error}";
                }
                continue;
            }

            $questions[] = $this->toQuestionData($quiz->quiz_id, $row);
        }

        if ($errors !== []) {
            throw new RuntimeException(implode("\n", $errors));
        }

        if ($questions === []) {
            throw new RuntimeException('The CSV file has no question rows.');
        }

        DB::transaction(function () use ($questions) {
            foreach ($questions as $question) {
                QuizQuestion::create($question);
            }
        });

        return count($questions);
    }

    /** @return array<int, array<string, string>> Keyed by 1-based data row number (header is row 1). */
    private function parse(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');
        abort_unless($handle !== false, 422, 'Unable to read the uploaded CSV file.');

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new RuntimeException('The CSV file is empty.');
        }

        $header = array_map(fn ($col) => strtolower(trim($col)), $header);
        $missing = array_diff(self::REQUIRED_COLUMNS, $header);
        if ($missing !== []) {
            fclose($handle);
            throw new RuntimeException('Missing required column(s): ' . implode(', ', $missing));
        }

        $rows = [];
        $lineNumber = 1;
        while (($line = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($line === [null] || $line === false) {
                continue;
            }

            // Pad/truncate to header length so array_combine never fails on ragged rows.
            $line = array_pad(array_slice($line, 0, count($header)), count($header), '');
            $rows[$lineNumber] = array_combine($header, $line);
        }
        fclose($handle);

        return $rows;
    }

    /** @return string[] Empty array means the row is valid. */
    private function validateRow(array $row): array
    {
        $errors = [];

        $questionText = trim($row['question_text'] ?? '');
        if ($questionText === '') {
            $errors[] = 'question_text is required.';
        }

        $type = strtolower(trim($row['question_type'] ?? ''));
        if (! in_array($type, self::VALID_TYPES, true)) {
            $errors[] = 'question_type must be one of: ' . implode(', ', self::VALID_TYPES) . '.';

            return $errors; // Can't validate the rest without knowing the type.
        }

        $correctAnswer = trim($row['correct_answer'] ?? '');
        if ($correctAnswer === '') {
            $errors[] = 'correct_answer is required.';
        }

        if ($type === 'multiple_choice') {
            $options = $this->optionList($row);
            if (count($options) < 2) {
                $errors[] = 'multiple_choice questions need at least 2 non-empty options.';
            } elseif ($correctAnswer !== '' && ! in_array(strtolower($correctAnswer), array_map('strtolower', $options), true)) {
                $errors[] = 'correct_answer must match one of the provided options.';
            }
        }

        if ($type === 'true_false' && ! in_array(strtolower($correctAnswer), ['true', 'false'], true)) {
            $errors[] = 'correct_answer for true_false must be "true" or "false".';
        }

        return $errors;
    }

    private function optionList(array $row): array
    {
        return array_values(array_filter([
            trim($row['option_a'] ?? ''),
            trim($row['option_b'] ?? ''),
            trim($row['option_c'] ?? ''),
            trim($row['option_d'] ?? ''),
        ], fn ($opt) => $opt !== ''));
    }

    private function toQuestionData(int $quizId, array $row): array
    {
        $type = strtolower(trim($row['question_type']));

        return [
            'quiz_id' => $quizId,
            'question_text' => trim($row['question_text']),
            'question_type' => $type,
            'options' => $type === 'multiple_choice' ? $this->optionList($row) : null,
            'correct_answer' => trim($row['correct_answer']),
        ];
    }
}
