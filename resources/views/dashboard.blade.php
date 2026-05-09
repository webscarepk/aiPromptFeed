@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')
@php
    $recentPrompts = \App\Models\AiPrompt::with(['category','type'])->latest()->take(6)->get();
    $totalPrompts   = \App\Models\AiPrompt::count();
    $totalCats      = \App\Models\Category::count();
    $totalTypes     = \App\Models\Type::count();
    $totalModels    = \App\Models\AiModel::count();
@endphp

{{-- Stats Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
    @foreach([
        ['label'=>'AI Prompts','value'=>$totalPrompts,'color'=>'blue','icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ['label'=>'Categories','value'=>$totalCats,'color'=>'purple','icon'=>'M4 6h16M4 10h16M4 14h16M4 18h16'],
        ['label'=>'Types','value'=>$totalTypes,'color'=>'indigo','icon'=>'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343'],
        ['label'=>'AI Models','value'=>$totalModels,'color'=>'emerald','icon'=>'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18'],
    ] as $stat)
    <div class="card p-6 flex items-center gap-4 group hover:border-{{ $stat['color'] }}-500/40 transition duration-300">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-{{ $stat['color'] }}-400"
             style="background: rgba(var(--tw-color-{{ $stat['color'] }}-500, 59 130 246)/0.1)">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/></svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ $stat['label'] }}</p>
            <p class="text-3xl font-bold text-white mt-0.5">{{ $stat['value'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Section Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-white">Recently Added Prompts</h2>
        <p class="text-sm text-gray-500 mt-0.5">Latest AI prompts added to the system</p>
    </div>
    <a href="{{ route('ai-prompts.index') }}"
       class="text-sm font-semibold text-blue-400 hover:text-blue-300 transition flex items-center gap-1.5">
        View all
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>

{{-- Recent Prompts Grid --}}
@if($recentPrompts->isEmpty())
    <div class="card p-16 text-center">
        <div class="text-5xl mb-4">✨</div>
        <p class="text-gray-400 font-medium">No prompts yet.</p>
        <p class="text-gray-600 text-sm mt-1">Create your first AI prompt to see it here.</p>
        <a href="{{ route('ai-prompts.index') }}"
           class="inline-flex items-center mt-5 px-5 py-2.5 rounded-xl text-sm font-semibold text-white"
           style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
            + Add AI Prompt
        </a>
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($recentPrompts as $prompt)
        <div class="card overflow-hidden flex flex-col group hover:border-blue-500/30 transition duration-300">
            {{-- Image --}}
            <div class="aspect-video bg-gray-900/60 relative overflow-hidden">
                @if($prompt->compressed_image)
                    <img src="{{ asset('storage/' . $prompt->compressed_image) }}" alt=""
                         class="w-full h-full object-cover transition duration-500 group-hover:scale-105 opacity-90">
                    <span class="absolute top-2 right-2 bg-green-500/80 text-[10px] text-white px-1.5 py-0.5 rounded-md backdrop-blur-sm">CMP</span>
                @elseif($prompt->image)
                    <img src="{{ asset('storage/' . $prompt->image) }}" alt=""
                         class="w-full h-full object-cover transition duration-500 group-hover:scale-105 opacity-90">
                @else
                    <div class="flex items-center justify-center h-full">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center"
                             style="background: rgba(59,130,246,0.1);">
                            <svg class="w-6 h-6 text-blue-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
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

            {{-- Body --}}
            <div class="p-5 flex-grow flex flex-col">
                <p class="text-sm text-gray-300 leading-relaxed line-clamp-2 italic flex-grow">
                    "{{ $prompt->prompt }}"
                </p>
                @if($prompt->description)
                    <p class="text-xs text-gray-600 line-clamp-1 mt-2">{{ $prompt->description }}</p>
                @endif
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/5">
                    <span class="text-xs text-gray-600">{{ $prompt->created_at->diffForHumans() }}</span>
                    <a href="{{ route('ai-prompts.index') }}"
                       class="text-xs font-semibold text-blue-400 hover:text-blue-300 transition">Edit →</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
