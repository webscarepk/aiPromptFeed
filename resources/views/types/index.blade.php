@extends('layouts.app')
@section('page-title', 'Types')

@section('content')
<div x-data="typeModal()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-white">Types</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $types->total() }} types total</p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:-translate-y-0.5"
            style="background: linear-gradient(135deg, #2563eb, #7c3aed); box-shadow: 0 4px 20px rgba(59,130,246,0.3);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Type
        </button>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('types.index') }}" class="flex gap-3 mb-6">
        <div class="relative flex-grow max-w-sm">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="input-search w-full pl-11 pr-4 py-2.5 text-sm"
                   placeholder="Search types...">
        </div>
        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white rounded-xl transition"
                style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">Search</button>
        @if(request('search'))
            <a href="{{ route('types.index') }}" class="px-5 py-2.5 text-sm font-medium text-red-400 rounded-xl transition"
               style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">✕ Clear</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        <table class="w-full text-left">
            <thead style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.06);">
                <tr>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Prompts</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                <tr class="transition hover:bg-white/[0.02]" style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                    <td class="px-6 py-4 text-gray-600 text-sm">{{ $types->firstItem() + $loop->index }}</td>
                    <td class="px-6 py-4">
                        @if($type->image)
                            <img src="{{ asset('storage/' . $type->image) }}" class="w-10 h-10 rounded-xl object-cover" style="border: 1px solid rgba(255,255,255,0.08);">
                        @else
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-600" style="background: rgba(255,255,255,0.04);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343"/></svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-white">{{ $type->name }}</p>
                        @if($type->description)
                            <p class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $type->description }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <code class="text-xs text-gray-400 px-2 py-1 rounded-lg" style="background: rgba(255,255,255,0.05);">{{ $type->slug }}</code>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-indigo-400">{{ $type->aiPrompts()->count() }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-3">
                            <button @click="openEdit({{ $type->id }}, {{ json_encode($type->name) }}, {{ json_encode($type->description ?? '') }}, {{ json_encode($type->image ? asset('storage/'.$type->image) : '') }})"
                                    title="Edit"
                                    class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form action="{{ route('types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-16 text-center text-gray-600">
                    <div class="text-4xl mb-3">🏷️</div>No types found.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($types->hasPages())
    <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $types->firstItem() }}–{{ $types->lastItem() }} of {{ $types->total() }}</p>
        <div class="flex items-center gap-1">
            @if($types->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-600 cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $types->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">← Prev</a>
            @endif
            @foreach($types->getUrlRange(max(1,$types->currentPage()-2), min($types->lastPage(),$types->currentPage()+2)) as $page => $url)
                @if($page == $types->currentPage())
                    <span class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background: linear-gradient(135deg,#2563eb,#7c3aed);">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">{{ $page }}</a>
                @endif
            @endforeach
            @if($types->hasMorePages())
                <a href="{{ $types->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">Next →</a>
            @else
                <span class="px-3 py-2 text-sm text-gray-600 cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
    @endif

    {{-- MODAL --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;"
         @keydown.escape.window="closeModal()">
        <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="closeModal()"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto z-10 rounded-2xl"
             style="background: #0d1117; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 80px rgba(0,0,0,0.8);">
            <div class="flex items-center justify-between p-6 sticky top-0 z-10"
                 style="background: #0d1117; border-bottom: 1px solid rgba(255,255,255,0.06);">
                <h2 class="text-lg font-bold text-white" x-text="isEdit ? '✏️ Edit Type' : '🏷️ New Type'"></h2>
                <button @click="closeModal()" class="text-gray-500 hover:text-white transition p-2 rounded-xl hover:bg-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form :action="isEdit ? '/types/' + editId : '{{ route('types.store') }}'" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" class="input-search w-full px-4 py-2.5 text-sm rounded-xl" placeholder="e.g. Image, Text, 3D" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description <span class="text-gray-600 font-normal normal-case">(optional)</span></label>
                    <textarea name="description" x-model="form.description" rows="2" class="input-search w-full px-4 py-3 text-sm resize-none rounded-xl" placeholder="Short description..."></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Image <span class="text-gray-600 font-normal normal-case">(optional)</span></label>
                    <div class="flex items-center gap-4">
                        <div x-show="form.currentImage || previewUrl" class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0" style="border: 1px solid rgba(255,255,255,0.1);">
                            <img :src="previewUrl || form.currentImage" class="w-full h-full object-cover">
                        </div>
                        <label class="flex-grow flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer transition"
                               style="background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,255,255,0.1);"
                               onmouseover="this.style.borderColor='rgba(59,130,246,0.5)'"
                               onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                            <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-sm font-medium text-blue-400" x-text="(previewUrl || form.currentImage) ? 'Change Image' : 'Upload Image'"></span>
                            <input type="file" name="image" class="hidden" accept="image/*" @change="onImageChange($event)">
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2" style="border-top: 1px solid rgba(255,255,255,0.06);">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-white rounded-xl transition"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">Cancel</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white rounded-xl transition hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #2563eb, #7c3aed);"
                            x-text="isEdit ? 'Update Type' : 'Save Type'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function typeModal() {
    return {
        open: false, isEdit: false, editId: null, previewUrl: '',
        form: { name: '', description: '', currentImage: '' },
        openCreate() { this.isEdit=false; this.editId=null; this.previewUrl=''; this.form={name:'',description:'',currentImage:''}; this.open=true; document.body.style.overflow='hidden'; },
        openEdit(id,name,description,currentImage) { this.isEdit=true; this.editId=id; this.previewUrl=''; this.form={name,description,currentImage}; this.open=true; document.body.style.overflow='hidden'; },
        closeModal() { this.open=false; this.previewUrl=''; document.body.style.overflow=''; },
        onImageChange(e) { const f=e.target.files[0]; if(f) this.previewUrl=URL.createObjectURL(f); }
    }
}
</script>
@endsection
