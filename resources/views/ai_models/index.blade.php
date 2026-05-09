@extends('layouts.app')

@section('content')
    <div x-data="aiModelModal()">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white">AI Models</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $aiModels->total() }} models total</p>
            </div>
            <button @click="openCreate()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:-translate-y-0.5"
                style="background: linear-gradient(135deg, #2563eb, #7c3aed); box-shadow: 0 4px 20px rgba(59,130,246,0.3);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add AI Model
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl">
                <div class="flex items-center gap-2 text-red-400 mb-2 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Failed to save AI Model
                </div>
                <ul class="list-disc list-inside text-sm text-red-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Search --}}
        <form method="GET" action="{{ route('ai-models.index') }}" class="flex gap-3 mb-6">
            <div class="relative flex-grow max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="input-search w-full pl-11 pr-4 py-2.5 text-sm" placeholder="Search models...">
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white rounded-xl transition"
                style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">Search</button>
            @if(request('search'))
                <a href="{{ route('ai-models.index') }}"
                    class="px-5 py-2.5 text-sm font-medium text-red-400 rounded-xl transition"
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
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aiModels as $aiModel)
                        <tr class="transition hover:bg-white/[0.02]" style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td class="px-6 py-4 text-gray-600 text-sm">{{ $aiModels->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                @if($aiModel->image)
                                    <img src="{{ asset('storage/' . $aiModel->image) }}" class="w-10 h-10 rounded-xl object-cover"
                                        style="border: 1px solid rgba(255,255,255,0.08);">
                                @else
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-600"
                                        style="background: rgba(255,255,255,0.04);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-white">{{ $aiModel->name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-300">{{ $aiModel->cost_per_use }} credits</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($aiModel->is_active)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-500/10 text-green-400 rounded-lg border border-green-500/20">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-red-500/10 text-red-400 rounded-lg border border-red-500/20">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        @click="openEdit({{ $aiModel->id }}, {{ json_encode($aiModel->name) }}, {{ json_encode($aiModel->description ?? '') }}, {{ json_encode($aiModel->api_key ?? '') }}, {{ json_encode($aiModel->image ? asset('storage/' . $aiModel->image) : '') }}, {{ json_encode($aiModel->api_endpoint ?? '') }}, {{ json_encode($aiModel->webhook_secret ?? '') }}, {{ $aiModel->cost_per_use }}, {{ $aiModel->is_active ? 'true' : 'false' }})"
                                        title="Edit"
                                        class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form action="{{ route('ai-models.destroy', $aiModel) }}" method="POST"
                                        onsubmit="return confirm('Delete this model?')">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-600">
                                <div class="text-4xl mb-3">🤖</div>No AI models found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($aiModels->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-500">Showing {{ $aiModels->firstItem() }}–{{ $aiModels->lastItem() }} of
                    {{ $aiModels->total() }}</p>
                <div class="flex items-center gap-1">
                    @if($aiModels->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-600 cursor-not-allowed">← Prev</span>
                    @else
                        <a href="{{ $aiModels->previousPageUrl() }}"
                            class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">←
                            Prev</a>
                    @endif
                    @foreach($aiModels->getUrlRange(max(1, $aiModels->currentPage() - 2), min($aiModels->lastPage(), $aiModels->currentPage() + 2)) as $page => $url)
                        @if($page == $aiModels->currentPage())
                            <span class="px-3 py-2 text-sm font-semibold text-white rounded-lg"
                                style="background: linear-gradient(135deg,#2563eb,#7c3aed);">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                                class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($aiModels->hasMorePages())
                        <a href="{{ $aiModels->nextPageUrl() }}"
                            class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">Next
                            →</a>
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
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto z-10 rounded-2xl"
                style="background: #0d1117; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 80px rgba(0,0,0,0.8);">
                <div class="flex items-center justify-between p-6 sticky top-0 z-10"
                    style="background: #0d1117; border-bottom: 1px solid rgba(255,255,255,0.06);">
                    <h2 class="text-lg font-bold text-white" x-text="isEdit ? '✏️ Edit AI Model' : '🤖 New AI Model'"></h2>
                    <button @click="closeModal()"
                        class="text-gray-500 hover:text-white transition p-2 rounded-xl hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form :action="isEdit ? '/ai-models/' + editId : '{{ route('ai-models.store') }}'" method="POST"
                    enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="form.name"
                            class="input-search w-full px-4 py-2.5 text-sm rounded-xl" placeholder="e.g. GPT-4, Midjourney"
                            required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description
                            <span class="text-gray-600 font-normal normal-case">(optional)</span></label>
                        <textarea name="description" x-model="form.description" rows="2"
                            class="input-search w-full px-4 py-3 text-sm resize-none rounded-xl"
                            placeholder="Short description..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">API Key <span
                                class="text-gray-600 font-normal normal-case">(optional)</span></label>
                        <div class="relative">
                            <input :type="showKey ? 'text' : 'password'" name="api_key" x-model="form.api_key"
                                class="input-search w-full px-4 py-2.5 pr-12 text-sm rounded-xl font-mono"
                                placeholder="sk-...">
                            <button type="button" @click="showKey = !showKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition">
                                <svg x-show="!showKey" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Image <span
                                class="text-gray-600 font-normal normal-case">(optional)</span></label>
                        <div class="flex items-center gap-4">
                            <div x-show="form.currentImage || previewUrl"
                                class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0"
                                style="border: 1px solid rgba(255,255,255,0.1);">
                                <img :src="previewUrl || form.currentImage" class="w-full h-full object-cover">
                            </div>
                            <label class="flex-grow flex items-center gap-3 px-4 py-3 rounded-xl cursor-pointer transition"
                                style="background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,255,255,0.1);"
                                onmouseover="this.style.borderColor='rgba(59,130,246,0.5)'"
                                onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'">
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-sm font-medium text-blue-400"
                                    x-text="(previewUrl || form.currentImage) ? 'Change Image' : 'Upload Image'"></span>
                                <input type="file" name="image" class="hidden" accept="image/*"
                                    @change="onImageChange($event)">
                            </label>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cost per Use (Credits)</label>
                            <input type="number" name="cost_per_use" x-model="form.cost_per_use" class="input-search w-full px-4 py-2.5 text-sm rounded-xl bg-gray-800 text-white border border-gray-700" min="1" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">API Endpoint</label>
                            <input type="text" name="api_endpoint" x-model="form.api_endpoint" class="input-search w-full px-4 py-2.5 text-sm rounded-xl bg-gray-800 text-white border border-gray-700" placeholder="https://api.example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Webhook Secret</label>
                            <input type="text" name="webhook_secret" x-model="form.webhook_secret" class="input-search w-full px-4 py-2.5 text-sm rounded-xl bg-gray-800 text-white border border-gray-700" placeholder="whsec_...">
                        </div>
                    </div>

                    <div class="mt-6 flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-300">Active</span>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-end gap-3 pt-2"
                        style="border-top: 1px solid rgba(255,255,255,0.06);">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-white rounded-xl transition"
                            style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2.5 text-sm font-bold text-white rounded-xl transition hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg, #2563eb, #7c3aed);"
                            x-text="isEdit ? 'Update Model' : 'Save Model'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function aiModelModal() {
            return {
                open: false, isEdit: false, editId: null, previewUrl: '', showKey: false,
                form: { name: '', description: '', api_key: '', currentImage: '', api_endpoint: '', webhook_secret: '', cost_per_use: 1, is_active: true },
                openCreate() {
                    this.isEdit = false; this.editId = null; this.previewUrl = ''; this.showKey = false;
                    this.form = { name: '', description: '', api_key: '', currentImage: '', api_endpoint: '', webhook_secret: '', cost_per_use: 1, is_active: true };
                    this.open = true; document.body.style.overflow = 'hidden';
                },
                openEdit(id, name, description, api_key, currentImage, api_endpoint, webhook_secret, cost_per_use, is_active) {
                    this.isEdit = true; this.editId = id; this.previewUrl = ''; this.showKey = false;
                    this.form = { name, description, api_key, currentImage, api_endpoint, webhook_secret, cost_per_use, is_active };
                    this.open = true; document.body.style.overflow = 'hidden';
                },
                closeModal() { this.open = false; this.previewUrl = ''; document.body.style.overflow = ''; },
                onImageChange(e) { const f = e.target.files[0]; if (f) this.previewUrl = URL.createObjectURL(f); }
            }
        }
    </script>
@endsection