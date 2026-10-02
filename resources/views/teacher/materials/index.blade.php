{{-- Teacher: Learning Materials --}}
@extends('layouts.teacher')
@section('title', 'Learning Materials')

@section('styles')
    @include('partials.styles.bento')
@endsection

@php
    $statuses = ['Draft', 'Published', 'Archived'];

    $label = 'mb-1.5 block text-sm font-medium text-ink/80 dark:text-slate-300';
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-ink placeholder-slate-400 shadow-xs transition focus:border-brand focus:outline-none focus:ring-3 focus:ring-brand/20 dark:border-slate-600 dark:bg-slate-900 dark:text-white dark:placeholder-slate-500 dark:focus:border-blue-400';
    $error = 'mt-1 text-xs font-medium text-red-600 dark:text-red-400';
    $statusBadge = [
        'Draft' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        'Published' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        'Archived' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
    ];
    $iconBtn = 'flex h-8 w-8 items-center justify-center rounded-lg transition hover:scale-105 hover:text-white';
@endphp

@section('content')
<div class="bento-content"
     x-data="{
        formOpen: false,
        mode: 'create',
        editId: null,
        form: { schedule_id: '', title: '', status: 'Draft' },
        deleteTarget: { id: null, title: '' },
        updateUrl: @js(route('teacher.materials.update', '__ID__')),
        destroyUrl: @js(route('teacher.materials.destroy', '__ID__')),
        previewUrl: @js(route('materials.preview', '__ID__')),
        filePreview: null,
        existingFile: null,
        clearFilePreview() { if (this.filePreview) URL.revokeObjectURL(this.filePreview.url); this.filePreview = null; },
        onFileChange(event) {
            this.clearFilePreview();
            const file = event.target.files[0];
            if (!file) return;
            this.filePreview = {
                url: URL.createObjectURL(file),
                name: file.name,
                size: (file.size / 1024).toFixed(1) + ' KB',
                kind: file.type === 'application/pdf' ? 'pdf' : (file.type.startsWith('video/') ? 'video' : 'other'),
            };
        },
        openCreate() { this.mode = 'create'; this.editId = null; this.form = { schedule_id: '', title: '', status: 'Draft' }; this.clearFilePreview(); this.existingFile = null; this.formOpen = true; },
        openEdit(material) {
            this.mode = 'edit'; this.editId = material.material_id;
            this.form = { schedule_id: material.schedule_id, title: material.title, status: material.status };
            this.clearFilePreview();
            const url = this.previewUrl.replace('__ID__', material.material_id);
            const kind = material.file_ext === 'PDF' ? 'pdf' : (material.file_ext === 'MP4' ? 'video' : 'other');
            this.existingFile = { ext: material.file_ext, url, kind };
            this.formOpen = true;
        },
        async openDelete(material) {
            this.deleteTarget = { id: material.material_id, title: material.title };
            const result = await window.LmsSwal.fire({
                title: 'Delete material?',
                text: '“' + material.title + '” and its file will be removed permanently. This cannot be undone.',
                icon: 'warning',
                confirmButtonText: 'Delete',
                customClass: window.LmsSwal.classes(true),
            });
            if (result.isConfirmed) this.$nextTick(() => this.$refs.deleteForm.submit());
        },
        get formAction() { return this.mode === 'edit' ? this.updateUrl.replace('__ID__', this.editId) : @js(route('teacher.materials.store')); }
     }"
     @keydown.escape.window="formOpen = false">

    {{-- Page header --}}
    <div class="bento-header flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold tracking-[-.4px] text-ink dark:text-white">Learning Materials</h1>
            <p class="mt-1 text-sm text-ink/60 dark:text-gray-400">Upload and organize modules, slides, and self-learning kits for your classes.</p>
        </div>
        <button type="button" @click="openCreate()" class="btn-navy">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/></svg>
            Upload Material
        </button>
    </div>

    {{-- Stats --}}
    <div class="bento-grid !grid-cols-2 lg:!grid-cols-4">
        @foreach ([
            ['Total', $stats['total'], 'text-ink dark:text-white'],
            ['Draft', $stats['draft'], 'text-ink/60 dark:text-slate-300'],
            ['Published', $stats['published'], 'text-emerald-600 dark:text-emerald-400'],
            ['Archived', $stats['archived'], 'text-amber-600 dark:text-amber-400'],
        ] as [$statLabel, $statValue, $statColor])
            <article class="bento-card !min-h-0">
                <div class="bento-card__label">{{ $statLabel }}</div>
                <div class="bento-card__value {{ $statColor }}">{{ $statValue }}</div>
            </article>
        @endforeach
    </div>

    {{-- Materials table --}}
    <div class="bento-grid">
        <article class="bento-card bento-card--full">
            <form method="GET" action="{{ route('teacher.materials.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <select name="schedule_id" class="{{ $input }}" onchange="this.form.submit()" aria-label="Filter by class">
                    <option value="">All classes</option>
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->schedule_id }}" @selected((string) request('schedule_id') === (string) $schedule->schedule_id)>
                            {{ $schedule->subject?->subject_name }} — {{ $schedule->section?->section_name }}
                        </option>
                    @endforeach
                </select>
                <select name="status" class="{{ $input }}" onchange="this.form.submit()" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </form>

            <div class="st-table-wrap mt-3">
                <table class="st-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $material)
                            @php
                                $payload = $material->only(['material_id', 'schedule_id', 'title', 'status']);
                                $payload['file_ext'] = strtoupper(pathinfo($material->file_url, PATHINFO_EXTENSION));
                            @endphp
                            <tr>
                                <td class="font-semibold text-ink dark:text-white">
                                    {{ $material->title }}
                                    <span class="ml-1 rounded bg-ink/5 px-1.5 py-0.5 text-[10px] font-bold text-ink/50 dark:bg-white/10 dark:text-slate-400">{{ $payload['file_ext'] }}</span>
                                </td>
                                <td>
                                    {{ $material->schedule?->subject?->subject_name }}
                                    <span class="text-ink/35">·</span>
                                    {{ $material->schedule?->section?->section_name }}
                                </td>
                                <td>
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$material->status] ?? '' }}">{{ $material->status }}</span>
                                </td>
                                <td class="whitespace-nowrap">{{ $material->uploaded_at?->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('materials.preview', $material->material_id) }}" target="_blank" rel="noopener" title="Preview" aria-label="Preview {{ $material->title }}"
                                           class="{{ $iconBtn }} bg-slate-500/10 text-slate-600 hover:bg-slate-500 dark:text-slate-300">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('materials.download', $material->material_id) }}" title="Download" aria-label="Download {{ $material->title }}"
                                           class="{{ $iconBtn }} bg-emerald-500/10 text-emerald-600 hover:bg-emerald-500 dark:text-emerald-300">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                        </a>
                                        <button type="button" @click="openEdit(@js($payload))" title="Edit" aria-label="Edit {{ $material->title }}"
                                                class="{{ $iconBtn }} bg-brand/10 text-brand hover:bg-brand dark:text-blue-300">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                        </button>
                                        <button type="button" @click="openDelete(@js($payload))" title="Delete" aria-label="Delete {{ $material->title }}"
                                                class="{{ $iconBtn }} bg-red-500/10 text-red-600 hover:bg-red-500 dark:text-red-300">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="st-table__empty">No materials uploaded yet. Click "Upload Material" to add your first resource.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($materials->hasPages())
                <div class="mt-3 border-t border-[#eef1f8] pt-3 dark:border-white/10">
                    {{ $materials->links() }}
                </div>
            @endif
        </article>
    </div>

    {{-- Delete: confirmed with the themed dialog, then this form is submitted --}}
    <form x-ref="deleteForm" method="POST" :action="destroyUrl.replace('__ID__', deleteTarget.id)" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    {{-- Upload / Edit modal --}}
    <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="material-form-title">
        <div x-show="formOpen" x-transition.opacity @click="formOpen = false; clearFilePreview()" class="absolute inset-0 bg-ink/45 backdrop-blur-sm"></div>
        <form x-show="formOpen"
              x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
              method="POST" :action="formAction" enctype="multipart/form-data"
              class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-[#e3e8f4] bg-linear-to-b from-white to-[#f7f9fe] shadow-[0_24px_60px_-12px_rgb(22_36_79/0.3)] dark:border-[#24386f] dark:from-[#0f1a38] dark:to-[#0f1a38]">
            @csrf
            <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

            <div class="flex items-start justify-between gap-4 border-b border-[#eef1f8] px-6 py-4 dark:border-[#24386f]">
                <div class="flex items-center gap-3">
                    <span class="st-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2M17 8l-5-5-5 5M12 3v12"/></svg></span>
                    <h2 id="material-form-title" class="font-display text-lg font-bold text-ink dark:text-white" x-text="mode === 'edit' ? 'Edit Material' : 'Upload Material'"></h2>
                </div>
                <button type="button" @click="formOpen = false; clearFilePreview()" aria-label="Close" class="flex h-8 w-8 items-center justify-center rounded-full text-xl text-ink/40 transition hover:bg-ink/5 hover:text-ink dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-5 overflow-y-auto px-6 py-5">
                <div x-show="mode === 'create'">
                    <label for="schedule_id" class="{{ $label }}">Class <span class="text-red-500">*</span></label>
                    <select id="schedule_id" name="schedule_id" x-model="form.schedule_id" class="{{ $input }}">
                        <option value="">Select a class</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->schedule_id }}">{{ $schedule->subject?->subject_name }} — {{ $schedule->section?->section_name }}</option>
                        @endforeach
                    </select>
                    @error('schedule_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="title" class="{{ $label }}">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" x-model="form.title" maxlength="255" required class="{{ $input }}" placeholder="e.g. Module 3 — Self-Learning Kit">
                    @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="{{ $label }}">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" x-model="form.status" class="{{ $input }}">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-ink/50 dark:text-slate-500">Only Published materials are visible to students.</p>
                    @error('status') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="file" class="{{ $label }}">File <span class="text-red-500" x-show="mode === 'create'">*</span></label>
                    <input type="file" id="file" name="file" :required="mode === 'create'" class="st-file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.mp4,.zip" @change="onFileChange($event)">
                    <p class="mt-1 text-xs text-ink/50 dark:text-slate-500">PDF, Office docs, video, or zip — max 20 MB. Leave blank when editing to keep the existing file.</p>
                    @error('file') <p class="{{ $error }}">{{ $message }}</p> @enderror

                    <template x-if="mode === 'edit' && existingFile && !filePreview">
                        <div class="mt-3 flex items-center gap-3 rounded-xl border border-[#e3e8f4] bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900/40">
                            <div class="flex h-16 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800">
                                <template x-if="existingFile.kind === 'pdf'">
                                    <iframe :src="existingFile.url" class="h-40 w-28 origin-top-left scale-[0.4] border-0" tabindex="-1"></iframe>
                                </template>
                                <template x-if="existingFile.kind === 'video'">
                                    <video :src="existingFile.url" muted class="h-full w-full object-cover"></video>
                                </template>
                                <template x-if="existingFile.kind === 'other'">
                                    <svg class="h-7 w-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </template>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink dark:text-slate-200">Current file <span class="text-ink/45" x-text="'(' + existingFile.ext + ')'"></span></p>
                                <p class="text-xs text-ink/50 dark:text-slate-400">Choose a new file above to replace it, or preview what's currently uploaded.</p>
                            </div>
                            <a :href="existingFile.url" target="_blank" rel="noopener" class="st-btn-outline btn-sm shrink-0">Preview</a>
                        </div>
                    </template>

                    <template x-if="filePreview">
                        <div class="mt-3 overflow-hidden rounded-xl border border-[#e3e8f4] dark:border-slate-700">
                            <template x-if="filePreview.kind === 'pdf'">
                                <iframe :src="filePreview.url" class="h-64 w-full bg-white"></iframe>
                            </template>
                            <template x-if="filePreview.kind === 'video'">
                                <video :src="filePreview.url" controls class="h-64 w-full bg-black"></video>
                            </template>
                            <template x-if="filePreview.kind === 'other'">
                                <div class="flex items-center gap-3 bg-white px-4 py-3 dark:bg-slate-900/40">
                                    <svg class="h-8 w-8 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-ink dark:text-slate-200" x-text="filePreview.name"></p>
                                        <p class="text-xs text-ink/50" x-text="filePreview.size + ' — no inline preview for this file type'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-[#eef1f8] bg-white/60 px-6 py-4 sm:flex-row sm:justify-end dark:border-[#24386f] dark:bg-slate-900/40">
                <button type="button" @click="formOpen = false; clearFilePreview()" class="st-btn-outline">Cancel</button>
                <button type="submit" class="btn-navy" x-text="mode === 'edit' ? 'Save Changes' : 'Upload'"></button>
            </div>
        </form>
    </div>
</div>
@endsection
