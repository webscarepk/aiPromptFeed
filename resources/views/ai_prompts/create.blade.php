@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex items-center space-x-4">
        <a href="{{ route('ai-prompts.index') }}" class="text-gray-400 hover:text-white transition">&larr; Back</a>
        <h1 class="text-3xl font-bold">{{ isset($aiPrompt) ? 'Edit AI Prompt' : 'Create AI Prompt' }}</h1>
    </div>

    <div class="bg-gray-800 rounded-2xl shadow-2xl overflow-hidden border border-gray-700">
        <form action="{{ isset($aiPrompt) ? route('ai-prompts.update', $aiPrompt) : route('ai-prompts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($aiPrompt))
                @method('PUT')
            @endif

            <div class="p-8 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                        <select name="category_id" id="category_id" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 transition" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $aiPrompt->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-300 mb-2">Type</label>
                        <select name="type_id" id="type_id" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 transition" required>
                            <option value="">Select Type</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('type_id', $aiPrompt->type_id ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Prompt -->
                <div>
                    <label for="prompt" class="block text-sm font-medium text-gray-300 mb-2">The Prompt</label>
                    <textarea name="prompt" id="prompt" rows="4" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 transition placeholder-gray-600" 
                              placeholder="Describe the prompt in detail..." required>{{ old('prompt', $aiPrompt->prompt ?? '') }}</textarea>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description (Optional)</label>
                    <textarea name="description" id="description" rows="2" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-blue-500 transition placeholder-gray-600" 
                              placeholder="Additional notes or instructions...">{{ old('description', $aiPrompt->description ?? '') }}</textarea>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-4">Preview Image</label>
                    <div class="flex items-center space-x-8">
                        @if(isset($aiPrompt) && $aiPrompt->image)
                            <div class="w-32 h-32 rounded-lg overflow-hidden border border-gray-700 flex-shrink-0">
                                <img src="{{ asset('storage/' . $aiPrompt->image) }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="flex-grow">
                            <label class="w-full flex flex-col items-center px-4 py-6 bg-gray-900 text-blue-400 rounded-lg border-2 border-dashed border-gray-700 cursor-pointer hover:border-blue-500 transition">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                <span class="mt-2 text-sm">Select Image</span>
                                <input type='file' name="image" class="hidden" />
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-gray-900/50 border-t border-gray-700 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-lg shadow-lg transform transition active:scale-95">
                    {{ isset($aiPrompt) ? 'Update Prompt' : 'Create Prompt' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
