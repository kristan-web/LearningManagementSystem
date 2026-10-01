<?php

namespace App\Http\Controllers;

use App\Models\SupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Support page requests: anyone signed in can send one; admins list and resolve them. */
class SupportRequestController extends Controller
{
    /** Same private disk the other uploads use. */
    private const DISK = 'local';

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:60'],
            'priority' => ['nullable', Rule::in(SupportRequest::PRIORITIES)],
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'image', 'max:5120'],
        ]);

        $file = $request->file('attachment');

        SupportRequest::create([
            'user_id' => $request->user()->user_id,
            'category' => $data['category'],
            'priority' => $data['priority'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'attachment_path' => $file?->store('support-requests/' . $request->user()->user_id, self::DISK),
            'attachment_name' => $file?->getClientOriginalName(),
            'status' => 'Open',
        ]);

        return redirect()->back()->with('success', 'Support request sent. We will reply to ' . $request->user()->email . '.');
    }

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['Open', 'Resolved', 'All'], true) ? $request->query('status') : 'Open';

        $requests = SupportRequest::with('user')
            ->when($status !== 'All', fn ($q) => $q->where('status', $status))
            ->latest()->paginate(15)->withQueryString();

        $counts = [
            'Open' => SupportRequest::where('status', 'Open')->count(),
            'Resolved' => SupportRequest::where('status', 'Resolved')->count(),
            'All' => SupportRequest::count(),
        ];

        return view('admin.support.requests', compact('requests', 'status', 'counts'));
    }

    public function update(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['Open', 'Resolved'])]]);

        $supportRequest->update([
            'status' => $data['status'],
            'resolved_at' => $data['status'] === 'Resolved' ? now() : null,
        ]);

        return redirect()->back()->with('success', $data['status'] === 'Resolved' ? 'Request marked as resolved.' : 'Request reopened.');
    }

    public function attachment(SupportRequest $supportRequest): StreamedResponse
    {
        abort_unless($supportRequest->attachment_path !== null, 404);

        return Storage::disk(self::DISK)->download($supportRequest->attachment_path, $supportRequest->attachment_name ?? 'attachment');
    }
}
