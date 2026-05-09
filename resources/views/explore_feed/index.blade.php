@extends('layouts.app')
@section('page-title', 'Explore Feed')

@section('content')
<div class="card p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Explore Feed</h2>
    </div>
    
    <table class="w-full text-left text-sm text-gray-400">
        <thead class="bg-gray-800 text-gray-300">
            <tr>
                <th class="px-4 py-3">Image</th>
                <th class="px-4 py-3">Prompt</th>
                <th class="px-4 py-3">Model</th>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Featured</th>
                <th class="px-4 py-3">Order</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr class="border-b border-gray-700">
                <td class="px-4 py-3">
                    <img src="{{ $item->image_url }}" alt="Result" class="w-12 h-12 rounded object-cover">
                </td>
                <td class="px-4 py-3 truncate max-w-[200px]" title="{{ $item->prompt }}">{{ Str::limit($item->prompt, 50) }}</td>
                <td class="px-4 py-3">{{ $item->aiModel->name ?? 'Unknown' }}</td>
                <td class="px-4 py-3">{{ $item->generationJob->user->name ?? 'Unknown' }}</td>
                <td class="px-4 py-3">{{ $item->is_featured ? 'Yes' : 'No' }}</td>
                <td class="px-4 py-3">{{ $item->display_order }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
