@extends('layouts.app')
@section('page-title', 'AI Prompts')

@section('content')
    <div x-data="promptModal()" x-init="initFromUrl()" @keydown.escape.window="closeModal()">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white">AI Prompts</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $aiPrompts->total() }} prompts total</p>
            </div>
            <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:-translate-y-0.5"
                style="background: linear-gradient(135deg, #2563eb, #7c3aed); box-shadow: 0 4px 20px rgba(59,130,246,0.3);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add AI Prompt
            </button>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('ai-prompts.index') }}" class="flex flex-wrap gap-3 mb-8">
            <div class="relative flex-grow min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="input-search w-full pl-11 pr-4 py-2.5 text-sm" placeholder="Search prompts...">
            </div>
            <select name="category_id" class="select-filter px-4 py-2.5 text-sm pr-10" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <select name="type_id" class="select-filter px-4 py-2.5 text-sm pr-10" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white rounded-xl transition"
                style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">
                Filter
            </button>
            @if(request()->hasAny(['search', 'category_id', 'type_id']))
                <a href="{{ route('ai-prompts.index') }}"
                    class="px-5 py-2.5 text-sm font-medium text-red-400 hover:text-red-300 rounded-xl transition"
                    style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">
                    ✕ Clear
                </a>
            @endif
        </form>

        {{-- Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse($aiPrompts as $prompt)
                <div class="card overflow-hidden flex flex-col group hover:border-blue-500/30 transition duration-300">
                    <div class="aspect-video bg-gray-900/60 relative overflow-hidden">
                        @if($prompt->image)
                            <img src="{{ asset('storage/' . $prompt->image) }}" alt=""
                                class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="flex items-center justify-center h-full">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                                    style="background: rgba(59,130,246,0.1);">
                                    <svg class="w-6 h-6 text-blue-500/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-3 left-3 flex gap-2">
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold text-purple-300"
                                style="background: rgba(139,92,246,0.2); border: 1px solid rgba(139,92,246,0.3);">
                                {{ $prompt->category->name }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md text-xs font-semibold text-blue-300"
                                style="background: rgba(59,130,246,0.2); border: 1px solid rgba(59,130,246,0.3);">
                                {{ $prompt->type->name }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5 flex-grow flex flex-col">
                        <p class="text-sm text-gray-300 leading-relaxed line-clamp-3 italic flex-grow">
                            "{{ $prompt->prompt }}"
                        </p>
                        @if($prompt->description)
                            <p class="text-xs text-gray-600 line-clamp-1 mt-2">{{ $prompt->description }}</p>
                        @endif
                    </div>

                    <div class="px-5 py-4 flex justify-between items-center"
                        style="background: rgba(255,255,255,0.02); border-top: 1px solid rgba(255,255,255,0.05);">
                        <span class="text-xs text-gray-600">{{ $prompt->created_at->diffForHumans() }}</span>
                        <div class="flex items-center gap-2">
                            <button
                                @click="openEdit({{ $prompt->id }}, {{ $prompt->category_id }}, {{ $prompt->type_id }}, {{ json_encode($prompt->prompt) }}, {{ json_encode($prompt->description ?? '') }}, {{ json_encode($prompt->image ? asset('storage/' . $prompt->image) : '') }})"
                                title="Edit"
                                class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <form action="{{ route('ai-prompts.destroy', $prompt) }}" method="POST"
                                onsubmit="return confirm('Delete this prompt?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete"
                                    class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full card p-16 text-center">
                    <div class="text-5xl mb-4">🔍</div>
                    <p class="text-gray-400 font-medium">No prompts found.</p>
                    <p class="text-gray-600 text-sm mt-1">Try adjusting your filters or create a new prompt.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($aiPrompts->hasPages())
            <div class="mt-8 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Showing {{ $aiPrompts->firstItem() }}–{{ $aiPrompts->lastItem() }} of {{ $aiPrompts->total() }}
                </p>
                <div class="flex items-center gap-1">
                    {{-- Prev --}}
                    @if($aiPrompts->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-600 rounded-lg cursor-not-allowed">← Prev</span>
                    @else
                        <a href="{{ $aiPrompts->previousPageUrl() }}"
                            class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">←
                            Prev</a>
                    @endif

                    @foreach($aiPrompts->getUrlRange(max(1, $aiPrompts->currentPage() - 2), min($aiPrompts->lastPage(), $aiPrompts->currentPage() + 2)) as $page => $url)
                        @if($page == $aiPrompts->currentPage())
                            <span class="px-3 py-2 text-sm font-semibold text-white rounded-lg"
                                style="background: linear-gradient(135deg, #2563eb, #7c3aed);">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if($aiPrompts->hasMorePages())
                        <a href="{{ $aiPrompts->nextPageUrl() }}"
                            class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">Next
                            →</a>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-600 rounded-lg cursor-not-allowed">Next →</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- ======== MODAL ======== --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">

            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="closeModal()"></div>

            <div x-show="open" x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto z-10 rounded-2xl"
                style="background: #0d1117; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 80px rgba(0,0,0,0.8);">

                <div class="flex items-center justify-between p-6 sticky top-0 z-10"
                    style="background: #0d1117; border-bottom: 1px solid rgba(255,255,255,0.06);">
                    <div>
                        <h2 class="text-lg font-bold text-white" x-text="isEdit ? '✏️ Edit AI Prompt' : '✨ New AI Prompt'">
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5"
                            x-text="isEdit ? 'Update prompt details' : 'Add a new prompt to the library'"></p>
                    </div>
                    <button @click="closeModal()"
                        class="text-gray-500 hover:text-white transition p-2 rounded-xl hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="isEdit ? '/ai-prompts/' + editId : '{{ route('ai-prompts.store') }}'" method="POST"
                    enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Category</label>
                            <select name="category_id" class="select-filter w-full px-4 py-2.5 text-sm rounded-xl" required>
                                <option value="">Select…</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" :selected="form.category_id == {{ $cat->id }}">
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Type</label>
                            <select name="type_id" class="select-filter w-full px-4 py-2.5 text-sm rounded-xl" required>
                                <option value="">Select…</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" :selected="form.type_id == {{ $type->id }}">
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Prompt <span
                                class="text-red-500">*</span></label>
                        <textarea name="prompt" rows="4" x-model="form.prompt"
                            class="input-search w-full px-4 py-3 text-sm resize-none rounded-xl"
                            placeholder="Write your AI prompt here..." required></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description
                            <span class="text-gray-600 font-normal normal-case">(optional)</span></label>
                        <textarea name="description" rows="2" x-model="form.description"
                            class="input-search w-full px-4 py-3 text-sm resize-none rounded-xl"
                            placeholder="Additional notes..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Preview Image
                            <span class="text-gray-600 font-normal normal-case">(optional)</span></label>
                        <div class="flex items-center gap-4">
                            <div x-show="previewUrl || (isEdit && form.currentImage)"
                                class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0"
                                style="border: 1px solid rgba(255,255,255,0.1);">
                                <img :src="previewUrl || form.currentImage" class="w-full h-full object-cover">
                            </div>
                            <label class="flex-grow flex items-center gap-3 px-4 py-4 rounded-xl cursor-pointer transition"
                                style="background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,255,255,0.1);"
                                onmouseover="this.style.borderColor='rgba(59,130,246,0.5)'"
                                onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-blue-400"
                                        x-text="(previewUrl || (isEdit && form.currentImage)) ? 'Change image' : 'Upload image'">
                                    </p>
                                    <p class="text-xs text-gray-600">PNG, JPG, GIF up to 2MB</p>
                                </div>
                                <input type="file" name="image" class="hidden" accept="image/*"
                                    @change="onImageChange($event)">
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2"
                        style="border-top: 1px solid rgba(255,255,255,0.06);">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-white rounded-xl transition"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold text-white rounded-xl transition hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #2563eb, #7c3aed); box-shadow: 0 4px 15px rgba(59,130,246,0.3);"
                            x-text="isEdit ? 'Update Prompt' : 'Create Prompt'">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function promptModal() {
            return {
                open: false, isEdit: false, editId: null, previewUrl: '',
                form: { category_id: '', type_id: '', prompt: '', description: '', currentImage: '' },

                openCreate() {
                    this.isEdit = false; this.editId = null; this.previewUrl = '';
                    this.form = { category_id: '', type_id: '', prompt: '', description: '', currentImage: '' };
                    this.open = true; document.body.style.overflow = 'hidden';
                },
                openEdit(id, category_id, type_id, prompt, description, currentImage) {
                    this.isEdit = true; this.editId = id; this.previewUrl = '';
                    this.form = { category_id, type_id, prompt, description, currentImage };
                    this.open = true; document.body.style.overflow = 'hidden';
                },
                closeModal() { this.open = false; this.previewUrl = ''; document.body.style.overflow = ''; },
                onImageChange(e) { const f = e.target.files[0]; if (f) this.previewUrl = URL.createObjectURL(f); },
                initFromUrl() { @if($errors->any()) this.openCreate(); @endif }
            }
        }
    </script>
@endsection