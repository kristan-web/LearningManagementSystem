<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\ClassSection;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnnouncementController extends Controller
{
    /** Same private disk convention as materials/submissions — never a guessable public URL. */
    private const DISK = 'local';

    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->role;

        $query = Announcement::with(['postedBy', 'section', 'attachments', 'comments']);

        $teacher = null;
        $student = null;
        $sections = collect();

        if ($role === 'Student') {
            $student = Student::where('user_id', $user->user_id)->first();
            $sectionId = $student?->activeEnrollment?->section_id;

            if ($sectionId) {
                $query->visibleToSection($sectionId);
            } else {
                $query->whereNull('section_id');
            }
        } elseif ($role === 'Teacher') {
            $teacher = Teacher::where('user_id', $user->user_id)->first();
            $teacherId = $teacher?->teacher_id ?? 0;

            // Teacher sees school-wide, sections they teach, or any announcement they posted
            $query->where(function ($q) use ($teacherId, $user) {
                $q->whereNull('section_id')
                    ->orWhere('posted_by', $user->user_id)
                    ->orWhereIn('section_id', function ($sub) use ($teacherId) {
                        $sub->select('section_id')->from('schedules')->where('teacher_id', $teacherId);
                    });
            });

            // Available sections for posting: sections where this teacher has schedules
            $sectionIds = Schedule::where('teacher_id', $teacherId)->pluck('section_id')->unique();
            $sections = ClassSection::whereIn('section_id', $sectionIds)->orderBy('section_name')->get();
        } elseif ($role === 'Admin') {
            // Admin sees all announcements and can target all sections
            $sections = ClassSection::orderBy('section_name')->get();
        } else {
            // Other roles (e.g. Registrar/Cashier/Guardian) see school-wide
            $query->whereNull('section_id');
        }

        if ($request->filled('section_id')) {
            if ($request->input('section_id') === 'school_wide') {
                $query->whereNull('section_id');
            } else {
                $query->where('section_id', $request->input('section_id'));
            }
        }

        $announcements = $query->orderByDesc('posted_at')->paginate(10)->withQueryString();

        return view('shared.announcements.index', compact('announcements', 'sections', 'role'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['Admin', 'Teacher'], true), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'section_id' => 'nullable|string', // can be 'school_wide' or integer
            'thumbnail' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif,webp',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:20480|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
        ]);

        $sectionId = null;
        if (!empty($validated['section_id']) && $validated['section_id'] !== 'school_wide') {
            $sectionId = (int) $validated['section_id'];

            // If teacher, verify they teach this section
            if ($user->role === 'Teacher') {
                $teacher = Teacher::where('user_id', $user->user_id)->firstOrFail();
                $teachesSection = Schedule::where('teacher_id', $teacher->teacher_id)
                    ->where('section_id', $sectionId)
                    ->exists();

                abort_unless($teachesSection, 403);
            } else {
                // If admin, ensure section exists
                abort_unless(ClassSection::where('section_id', $sectionId)->exists(), 422);
            }
        }

        $announcement = Announcement::create([
            'posted_by' => $user->user_id,
            'section_id' => $sectionId,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'posted_at' => now(),
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('announcements/' . $announcement->announcement_id, self::DISK);
            $announcement->update(['thumbnail_path' => $path]);
        }

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('announcements/' . $announcement->announcement_id . '/attachments', self::DISK);

            AnnouncementAttachment::create([
                'announcement_id' => $announcement->announcement_id,
                'file_name' => $file->getClientOriginalName(),
                'file_url' => $path,
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement published successfully.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['Admin', 'Teacher'], true), 403);

        // Admin can delete any; Teacher can only delete their own
        if ($user->role === 'Teacher') {
            abort_unless((int) $announcement->posted_by === (int) $user->user_id, 403);
        }

        if ($announcement->thumbnail_path) {
            Storage::disk(self::DISK)->delete($announcement->thumbnail_path);
        }

        foreach ($announcement->attachments as $attachment) {
            Storage::disk(self::DISK)->delete($attachment->file_url);
        }

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }

    /** Streams the thumbnail image inline — gated by the same visibility rule as the feed. */
    public function thumbnail(Request $request, Announcement $announcement): StreamedResponse
    {
        abort_unless($announcement->thumbnail_path && $announcement->isViewableBy($request->user()), 404);

        return Storage::disk(self::DISK)->response($announcement->thumbnail_path);
    }

    /** Downloads an attachment — gated by the parent announcement's visibility rule. */
    public function attachmentDownload(Request $request, AnnouncementAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->announcement?->isViewableBy($request->user()), 403);

        return Storage::disk(self::DISK)->download($attachment->file_url, $attachment->file_name);
    }
}
