@if (in_array(auth()->user()?->role, ['Admin', 'Teacher'], true))
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
            <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data">
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
                    <div>
                        <label for="thumbnail" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Thumbnail photo</label>
                        <input id="thumbnail" name="thumbnail" type="file" accept="image/*" class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200">
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG, GIF or WEBP, up to 5MB.</p>
                    </div>
                    <div>
                        <label for="attachments" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Attach files</label>
                        <input id="attachments" name="attachments[]" type="file" multiple class="block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200">
                        <p class="mt-1 text-xs text-slate-400">Up to 5 files, 20MB each.</p>
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
