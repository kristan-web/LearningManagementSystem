<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Message;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Direct messages between teachers and the students in their sections (one inbox for both roles). */
class MessageController extends Controller
{
    /** Same private disk the other uploads use. */
    private const DISK = 'local';

    public function index(Request $request): View
    {
        $user = $this->authorizedUser($request);
        $me = (int) $user->user_id;
        $contacts = $this->contactsFor($user);

        // Every message I sent or received, newest first; each other person is one conversation.
        $mine = Message::where('sender_id', $me)->orWhere('receiver_id', $me)
            ->orderByDesc('sent_at')->orderByDesc('message_id')->get();
        $partnerId = fn (Message $m) => (int) $m->sender_id === $me ? (int) $m->receiver_id : (int) $m->sender_id;
        $partners = User::whereIn('user_id', $mine->map($partnerId)->unique())->get()->keyBy('user_id');

        $search = mb_strtolower(trim((string) $request->query('search')));
        $conversations = $mine->groupBy($partnerId)
            ->filter(fn ($thread, $id) => $partners->has($id))
            ->map(fn ($thread, $id) => (object) [
                'user_id' => (int) $id,
                'name' => $this->displayName($partners[$id]),
                'role' => $partners[$id]->role,
                'last_message' => $thread->first()->body !== '' ? $thread->first()->body : 'Attachment: ' . $thread->first()->attachment_name,
                'last_sent_at' => $thread->first()->sent_at,
                'unread' => $thread->filter(fn (Message $m) => (int) $m->receiver_id === $me && $m->read_at === null)->count(),
            ])
            ->filter(fn ($c) => $search === '' || str_contains(mb_strtolower($c->name . ' ' . $c->last_message), $search))
            ->values();

        $withId = (int) $request->query('with');
        $withUser = $contacts->get($withId) ?? $partners->get($withId);
        $activeConversation = $withUser ? (object) [
            'user_id' => $withId,
            'name' => $this->displayName($withUser),
            'role' => $withUser->role,
        ] : null;

        $messages = collect();
        if ($activeConversation) {
            Message::where('sender_id', $withId)->where('receiver_id', $me)->whereNull('read_at')->update(['read_at' => now()]);
            $messages = $mine->filter(fn (Message $m) => $partnerId($m) === $withId)
                ->sortBy(fn (Message $m) => [$m->sent_at, $m->message_id])
                ->map(fn (Message $m) => (object) [
                    'id' => (int) $m->message_id,
                    'body' => $m->body,
                    'attachment_name' => $m->attachment_path ? $m->attachment_name : null,
                    'sent_at' => $m->sent_at,
                    'is_mine' => (int) $m->sender_id === $me,
                ])
                ->values();
        }

        // People the user can start a new conversation with.
        $newContacts = $contacts->map(fn (User $c) => (object) ['user_id' => (int) $c->user_id, 'name' => $this->displayName($c)])->values();
        $inboxRoute = $this->inboxRoute($user);

        return view('shared.messages.index', compact('conversations', 'activeConversation', 'messages', 'newContacts', 'inboxRoute'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->authorizedUser($request);
        $data = $request->validate([
            'receiver_id' => ['required', 'integer'],
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,jpg,jpeg,png'],
        ]);
        $me = (int) $user->user_id;
        $to = (int) $data['receiver_id'];

        // Allowed: a current contact, or someone already in a conversation with this user.
        $hasThread = Message::where(fn ($q) => $q->where('sender_id', $me)->where('receiver_id', $to))
            ->orWhere(fn ($q) => $q->where('sender_id', $to)->where('receiver_id', $me))
            ->exists();
        abort_unless($to !== $me && ($hasThread || $this->contactsFor($user)->has($to)), 403);

        $file = $request->file('attachment');
        Message::create([
            'sender_id' => $me,
            'receiver_id' => $to,
            'body' => trim((string) ($data['body'] ?? '')),
            'attachment_path' => $file?->store('messages/' . $me, self::DISK),
            'attachment_name' => $file?->getClientOriginalName(),
            'sent_at' => now(),
        ]);

        return redirect()->route($this->inboxRoute($user), ['with' => $to]);
    }

    /** Only the sender and the receiver can download a message's file. */
    public function attachment(Request $request, Message $message): StreamedResponse
    {
        $me = (int) $request->user()->user_id;
        abort_unless(in_array($me, [(int) $message->sender_id, (int) $message->receiver_id], true), 403);
        abort_unless($message->attachment_path !== null, 404);

        return Storage::disk(self::DISK)->download($message->attachment_path, $message->attachment_name ?? 'attachment');
    }

    private function authorizedUser(Request $request): User
    {
        abort_unless(in_array($request->user()->role, ['Teacher', 'Student'], true), 403);

        return $request->user();
    }

    /** Teachers: students enrolled in sections they teach. Students: teachers of their active section. */
    private function contactsFor(User $user): Collection
    {
        if ($user->role === 'Teacher') {
            $teacherId = Teacher::where('user_id', $user->user_id)->value('teacher_id');
            $sectionIds = Schedule::where('teacher_id', $teacherId)->pluck('section_id');
            $studentIds = Enrollment::whereIn('section_id', $sectionIds)->where('status', 'Enrolled')->pluck('student_id');
            $userIds = Student::whereIn('student_id', $studentIds)->pluck('user_id');
        } else {
            $sectionId = Student::where('user_id', $user->user_id)->first()?->activeEnrollment?->section_id;
            $teacherIds = Schedule::where('section_id', $sectionId)->whereNotNull('teacher_id')->pluck('teacher_id');
            $userIds = Teacher::whereIn('teacher_id', $teacherIds)->pluck('user_id');
        }

        return User::whereIn('user_id', $userIds)->where('is_deleted', false)
            ->orderBy('last_name')->orderBy('first_name')->get()
            ->keyBy(fn (User $u) => (int) $u->user_id);
    }

    private function displayName(User $user): string
    {
        return trim($user->first_name . ' ' . $user->last_name);
    }

    private function inboxRoute(User $user): string
    {
        return $user->role === 'Teacher' ? 'teacher.communication.index' : 'student.communication.index';
    }
}
