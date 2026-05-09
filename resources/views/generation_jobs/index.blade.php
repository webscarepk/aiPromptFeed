@extends('layouts.app')
@section('page-title', 'Generation Jobs')

@section('content')
<div class="card p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-white">Generation Jobs</h2>
    </div>
    
    <table class="w-full text-left text-sm text-gray-400">
        <thead class="bg-gray-800 text-gray-300">
            <tr>
                <th class="px-4 py-3">User</th>
                <th class="px-4 py-3">Model</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Credits</th>
                <th class="px-4 py-3">Result</th>
                <th class="px-4 py-3">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            <tr class="border-b border-gray-700">
                <td class="px-4 py-3 text-white">{{ $job->user->name ?? 'Unknown' }}</td>
                <td class="px-4 py-3">{{ $job->aiModel->name ?? 'Unknown' }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded text-xs {{ $job->status == 'completed' ? 'bg-green-900 text-green-300' : ($job->status == 'failed' ? 'bg-red-900 text-red-300' : 'bg-yellow-900 text-yellow-300') }}">
                        {{ $job->status }}
                    </span>
                </td>
                <td class="px-4 py-3">{{ $job->credits_consumed }}</td>
                <td class="px-4 py-3">
                    @if($job->result_image_url)
                        <a href="{{ $job->result_image_url }}" target="_blank" class="text-blue-400 hover:underline">View Image</a>
                    @else
                        -
                    @endif
                </td>
                <td class="px-4 py-3">{{ $job->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $jobs->links() }}</div>
</div>
@endsection
