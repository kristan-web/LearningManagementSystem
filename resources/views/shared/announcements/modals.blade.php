@if (in_array(auth()->user()?->role, ['Admin', 'Teacher'], true))
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
            <form method="POST" action="{{ route('announcements.store') }}">
                @csrf
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">New Announcement</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="title" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Title <span class="text-red-500">*</span></label>
                        <input id="title" name="title" type="text" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label for="modal_section_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Audience <span class="text-red-500">*</span></label>
                        <select id="modal_section_id" name="section_id" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                            <option value="school_wide">School-wide (All Users)</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->section_id }}">{{ $sec->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="body" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Content <span class="text-red-500">*</span></label>
                        <textarea id="body" name="body" rows="4" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="createOpen = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200">Cancel</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Publish</button>
                </div>
            </form>
        </div>
    </div>
@endif

<div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
        <form method="POST" :action="deleteAction">
            @csrf
            @method('DELETE')
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Delete Announcement?</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400" x-text="'Delete \u201c' + announcementTitle + '\u201d?'"></p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="deleteOpen = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200">Cancel</button>
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
            </div>
        </form>
    </div>
</div>

<div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
    <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
        <form method="POST" :action="editAction">
            @csrf
            @method('PUT')
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit Announcement</h2>
            <div class="mt-4 space-y-4">
                <div>
                    <label for="edit_title" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Title <span class="text-red-500">*</span></label>
                    <input id="edit_title" name="title" type="text" x-model="announcementTitle" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                </div>
                <div>
                    <label for="edit_section_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Audience <span class="text-red-500">*</span></label>
                    <select id="edit_section_id" name="section_id" x-model="announcementSection" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                        <option value="school_wide">School-wide (All Users)</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec->section_id }}">{{ $sec->section_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_body" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Content <span class="text-red-500">*</span></label>
                    <textarea id="edit_body" name="body" x-model="announcementBody" rows="4" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-600 dark:bg-slate-900 dark:text-white"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="editOpen = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200">Cancel</button>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

