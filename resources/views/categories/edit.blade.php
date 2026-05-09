@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8 flex items-center space-x-4">
        <a href="{{ route('categories.index') }}" class="text-gray-400 hover:text-white transition">&larr; Back</a>
        <h1 class="text-3xl font-bold">{{ isset($category) ? 'Edit Category' : 'Create Category' }}</h1>
    </div>

    <div class="bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
        <form action="{{ isset($category) ? route('categories.update', $category) : route('categories.store') }}" method="POST">
            @csrf
            @if(isset($category))
                @method('PUT')
            @endif

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Category Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}" 
                           class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="e.g. Photography, Digital Art" required>
                    @error('name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-lg shadow-lg transform transition active:scale-95">
                    {{ isset($category) ? 'Update Category' : 'Save Category' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
