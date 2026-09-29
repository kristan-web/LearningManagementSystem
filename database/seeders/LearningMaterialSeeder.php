<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Placeholder learning materials so the demo teacher ("teacher@school.com")
 * has something to manage and the demo student ("student@school.com") has
 * something to view/download. Reuses the demo schedule set up by
 * AssignmentSeeder, and assigns the demo teacher to it if it has none.
 *
 * Also seeds a handful of extra demo subjects on the same section/teacher so
 * the student's "Learning Materials" landing page has multiple subject
 * cards to click through, not just one.
 *
 * Safe to re-run: uses firstOrCreate and only assigns the teacher if the
 * schedule doesn't already have one.
 */
class LearningMaterialSeeder extends Seeder
{
    private const DISK = 'local';

    public function run(): void
    {
        $schedule = (new AssignmentSeeder())->demoSchedule();

        $teacherUser = User::where('email', 'teacher@school.com')->first();
        $teacher = $teacherUser ? Teacher::where('user_id', $teacherUser->user_id)->first() : null;

        if ($teacher && $schedule->teacher_id === null) {
            $schedule->update(['teacher_id' => $teacher->teacher_id]);
        }

        if (! $teacher || ! $teacherUser) {
            return;
        }

        $this->seedMaterials($schedule, $teacherUser, [
            ['title' => 'Week 1 Lecture Notes', 'status' => 'Published', 'content' => 'Placeholder lecture notes for Week 1.'],
            ['title' => 'Week 2 Draft Handout', 'status' => 'Draft', 'content' => 'Placeholder draft handout for Week 2 (not yet published).'],
        ]);

        foreach ($this->extraDemoSchedules($schedule, $teacher) as [$extraSchedule, $materials]) {
            $this->seedMaterials($extraSchedule, $teacherUser, $materials);
        }
    }

    /** @param array<int, array{title: string, status: string, content: string}> $materials */
    private function seedMaterials(Schedule $schedule, User $teacherUser, array $materials): void
    {
        foreach ($materials as $material) {
            $existing = LearningMaterial::where('schedule_id', $schedule->schedule_id)
                ->where('title', $material['title'])
                ->first();

            if ($existing) {
                continue;
            }

            $path = 'learning_materials/' . $schedule->schedule_id . '/' . Str::slug($material['title']) . '.txt';
            Storage::disk(self::DISK)->put($path, $material['content']);

            LearningMaterial::create([
                'schedule_id' => $schedule->schedule_id,
                'title' => $material['title'],
                'status' => $material['status'],
                'file_url' => $path,
                'uploaded_by' => $teacherUser->user_id,
            ]);
        }
    }

    /**
     * More subjects on the same demo section/teacher, each on a different
     * weekday (schedules require a unique section+subject pair). Returns
     * [Schedule, materials[]] pairs for seedMaterials() to consume.
     *
     * @return array<int, array{0: Schedule, 1: array<int, array{title: string, status: string, content: string}>}>
     */
    private function extraDemoSchedules(Schedule $demoSchedule, Teacher $teacher): array
    {
        $roomId = DB::table('rooms')->where('room_name', 'Room 1')->value('room_id');

        $subjects = [
            [
                'code' => 'MATH101', 'name' => 'General Mathematics', 'day' => 'Tuesday',
                'materials' => [
                    ['title' => 'Functions Overview', 'status' => 'Published', 'content' => 'Placeholder notes on functions.'],
                    ['title' => 'Rational Equations Worksheet', 'status' => 'Published', 'content' => 'Placeholder worksheet on rational equations.'],
                    ['title' => 'Quiz 1 Draft', 'status' => 'Draft', 'content' => 'Placeholder draft quiz (not yet published).'],
                ],
            ],
            [
                'code' => 'BIO101', 'name' => 'General Biology', 'day' => 'Wednesday',
                'materials' => [
                    ['title' => 'Cell Structure Slides', 'status' => 'Published', 'content' => 'Placeholder slides on cell structure.'],
                    ['title' => 'Ecosystems Reading', 'status' => 'Published', 'content' => 'Placeholder reading on ecosystems.'],
                ],
            ],
            [
                'code' => 'ICT101', 'name' => 'Empowerment Technologies', 'day' => 'Thursday',
                'materials' => [
                    ['title' => 'Intro to Spreadsheets', 'status' => 'Published', 'content' => 'Placeholder guide on spreadsheets.'],
                    ['title' => 'Online Safety Guide', 'status' => 'Published', 'content' => 'Placeholder guide on online safety.'],
                    ['title' => 'Web Design Draft Notes', 'status' => 'Draft', 'content' => 'Placeholder draft notes on web design.'],
                ],
            ],
            [
                'code' => 'ENG101', 'name' => 'Oral Communication', 'day' => 'Friday',
                'materials' => [
                    ['title' => 'Public Speaking Basics', 'status' => 'Published', 'content' => 'Placeholder notes on public speaking.'],
                ],
            ],
        ];

        return collect($subjects)->map(function (array $entry) use ($demoSchedule, $teacher, $roomId) {
            $subject = Subject::firstOrCreate(
                ['subject_code' => $entry['code']],
                [
                    'strand_id' => $demoSchedule->section->strand_id, 'subject_name' => $entry['name'],
                    'subject_type' => 'Core', 'grade_level' => '11', 'semester' => '1st Semester', 'units' => 1.0,
                ]
            );

            $schedule = Schedule::firstOrCreate(
                ['section_id' => $demoSchedule->section_id, 'subject_id' => $subject->subject_id],
                [
                    'teacher_id' => $teacher->teacher_id, 'room_id' => $roomId,
                    'day_of_week' => $entry['day'], 'start_time' => '09:00:00', 'end_time' => '10:00:00',
                ]
            );

            return [$schedule, $entry['materials']];
        })->all();
    }
}
