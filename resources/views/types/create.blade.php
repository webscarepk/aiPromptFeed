@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8 flex items-center space-x-4">
        <a href="{{ route('types.index') }}" class="text-gray-400 hover:text-white transition">&larr; Back</a>
        <h1 class="text-3xl font-bold">{{ isset($type) ? 'Edit Type' : 'Create Type' }}</h1>
    </div>

    <div class="bg-gray-800 rounded-xl shadow-2xl p-8 border border-gray-700">
        <form action="{{ isset($type) ? route('types.update', $type) : route('types.store') }}" method="POST">
            @csrf
            @if(isset($type))
                @method('PUT')
            @endif

            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Type Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $type->name ?? '') }}" 
                           class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                           placeholder="e.g. Image, Text, 3D" required>
                    @error('name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-lg shadow-lg transform transition active:scale-95">
                    {{ isset($type) ? 'Update Type' : 'Save Type' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
