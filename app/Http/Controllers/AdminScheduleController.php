<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminScheduleController extends Controller
{
    use AuthorizesRequests;
    private const DAY_ORDER = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Schedule::class);

        $schedules = Schedule::with(['section.strand','subject','teacher.user','room'])
            ->get()->sortBy(fn(Schedule $s)=>[
                $s->section?->section_name, array_search($s->day_of_week,self::DAY_ORDER), $s->start_time])->values();

        return view('admin.schedule.index', compact('schedules') + [
            'sections' => ClassSection::orderBy('section_name')->get(),
            'subjects' => Subject::orderBy('subject_name')->get(),
            'teachers' => Teacher::with('user')->get()->sortBy(fn(Teacher $t)=>$t->user?->last_name),
            'rooms'    => Room::orderBy('room_name')->get(),
            'days'     => self::DAY_ORDER,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Schedule::class);
        $data = $this->validateSchedule($request);
        if ($conflict = $this->detectConflict($data)) return back()->withErrors(['conflict'=>$conflict])->withInput();
        Schedule::create($data);
        return redirect()->route('admin.schedule.index')->with('success','Schedule period created successfully.');
    }

    public function update(Request $request, int $schedule_id): RedirectResponse
    {
        $this->authorize('create', Schedule::class);
        $schedule = Schedule::findOrFail($schedule_id);
        $this->authorize('update', $schedule);
        $data = $this->validateSchedule($request);
        if ($conflict = $this->detectConflict($data, $schedule->schedule_id)) return back()->withErrors(['conflict'=>$conflict])->withInput();
        $schedule->update($data);
        return redirect()->route('admin.schedule.index')->with('success','Schedule period updated successfully.');
    }

    public function destroy(Request $request, int $schedule_id): RedirectResponse
    {
        $this->authorize('create', Schedule::class);
        $schedule = Schedule::findOrFail($schedule_id);
        $this->authorize('delete', $schedule);
        $linked = $schedule->assignments()->count() + $schedule->quizzes()->count();
        if ($linked > 0) return back()->withErrors(['conflict'=>"This schedule period has {$linked} linked assignment(s)/quiz(zes) and cannot be deleted."]);
        $schedule->delete();
        return redirect()->route('admin.schedule.index')->with('success','Schedule period deleted.');
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'section_id' => 'required|integer|exists:class_sections,section_id',
            'subject_id' => 'required|integer|exists:subjects,subject_id',
            'teacher_id' => 'required|integer|exists:teachers,teacher_id',
            'room_id'    => 'nullable|integer|exists:rooms,room_id',
            'day_of_week'=> 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ]);
    }

    /** Overlap: existing.start < new.end AND existing.end > new.start */
    private function detectConflict(array $data, ?int $exclude = null): ?string
    {
        $base = Schedule::where('day_of_week', $data['day_of_week'])
            ->where('start_time','<', $data['end_time'])
            ->where('end_time','>', $data['start_time'])
            ->when($exclude, fn($q)=>$q->where('schedule_id','!=', $exclude));

        $c = (clone $base)->where('section_id', $data['section_id'])->first();
        if ($c) return "Section is already scheduled on {$data['day_of_week']} during that time slot ({$c->section?->section_name}).";

        $c = (clone $base)->where('teacher_id', $data['teacher_id'])->with('section')->first();
        if ($c) { $n = $c->section?->section_name ?? 'another section'; return "Teacher is already scheduled for {$n} on {$data['day_of_week']} during that time slot."; }

        if (!empty($data['room_id'])) {
            $c = (clone $base)->where('room_id', $data['room_id'])->with('section')->first();
            if ($c) { $n = $c->section?->section_name ?? 'another section'; return "Room is already booked for {$n} on {$data['day_of_week']} during that time slot."; }
        }
        return null;
    }
}
