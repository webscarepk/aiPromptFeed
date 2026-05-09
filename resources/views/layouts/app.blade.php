<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AiPromptFeed')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #111827;
        }

        ::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4B5563;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #2563eb20, #7c3aed20);
            border: 1px solid #2563eb40;
            color: #60a5fa;
        }

        .sidebar-link:not(.active) {
            color: #9ca3af;
        }

        .sidebar-link:not(.active):hover {
            background: rgba(255, 255, 255, 0.05);
            color: #f9fafb;
        }

        .sidebar-icon {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .card {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 16px;
        }

        .input-search {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: white;
            transition: all 0.2s;
            border-radius: 12px;
        }

        .input-search:focus {
            outline: none;
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.06);
        }

        .input-search::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .select-filter {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: white;
            border-radius: 12px;
        }

        .select-filter:focus {
            outline: none;
            border-color: rgba(59, 130, 246, 0.5);
        }

        .select-filter option {
            background: #1f2937;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            w-9 h-9 rounded-lg text-sm font-medium transition;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .flash-msg {
            animation: slideIn 0.3s ease-out;
        }

        .bg-app {
            background: #0a0f1a;
        }
    </style>
</head>

<body class="bg-app text-gray-100 antialiased">

    <div class="flex min-h-screen">

        {{-- ========== SIDEBAR ========== --}}
        <aside class="w-60 flex-shrink-0 fixed inset-y-0 left-0 z-40 flex flex-col"
            style="background: linear-gradient(180deg, #0d1117 0%, #0a0f1a 100%); border-right: 1px solid rgba(255,255,255,0.06);">

            {{-- Brand --}}
            <div class="p-5 flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <span class="text-base font-bold text-white">AiPromptFeed</span>
                    <span class="block text-xs text-gray-600">Admin Panel</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-grow px-3 pb-4 space-y-1 overflow-y-auto">
                <p class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-600 uppercase tracking-widest">Main</p>

                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('ai-prompts.index') }}"
                    class="sidebar-link {{ request()->routeIs('ai-prompts.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    AI Prompts
                    <span class="ml-auto text-xs bg-blue-600/20 text-blue-400 px-2 py-0.5 rounded-full font-medium">
                        {{ \App\Models\AiPrompt::count() }}
                    </span>
                </a>

                <a href="{{ route('users.index') }}"
                    class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users
                    <span class="ml-auto text-xs bg-emerald-600/20 text-emerald-400 px-2 py-0.5 rounded-full font-medium">
                        {{ \App\Models\User::count() }}
                    </span>
                </a>

                <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-600 uppercase tracking-widest">Taxonomy</p>

                <a href="{{ route('categories.index') }}"
                    class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Categories
                    <span class="ml-auto text-xs bg-purple-600/20 text-purple-400 px-2 py-0.5 rounded-full font-medium">
                        {{ \App\Models\Category::count() }}
                    </span>
                </a>

                <a href="{{ route('types.index') }}"
                    class="sidebar-link {{ request()->routeIs('types.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343" />
                    </svg>
                    Types
                    <span class="ml-auto text-xs bg-indigo-600/20 text-indigo-400 px-2 py-0.5 rounded-full font-medium">
                        {{ \App\Models\Type::count() }}
                    </span>
                </a>

                <a href="{{ route('ai-models.index') }}"
                    class="sidebar-link {{ request()->routeIs('ai-models.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18" />
                    </svg>
                    AI Models
                </a>
                
                <a href="{{ route('subscription-plans.index') }}"
                    class="sidebar-link {{ request()->routeIs('subscription-plans.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Plans
                </a>

                <a href="{{ route('generation-jobs.index') }}"
                    class="sidebar-link {{ request()->routeIs('generation-jobs.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Generations
                </a>

                <a href="{{ route('explore-feed.index') }}"
                    class="sidebar-link {{ request()->routeIs('explore-feed.*') ? 'active' : '' }}">
                    <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>
                    Explore Feed
                </a>
            </nav>

            {{-- User Footer --}}
            <div class="p-3 border-t" style="border-color: rgba(255,255,255,0.06);">
                <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/5 transition group">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0 text-white"
                        style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                            class="text-gray-500 hover:text-red-400 transition opacity-0 group-hover:opacity-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ========== MAIN ========== --}}
        <div class="flex-1 ml-60 flex flex-col min-h-screen">

            {{-- Top Bar --}}
            <header class="sticky top-0 z-30 flex items-center justify-between px-8 py-4"
                style="background: rgba(10,15,26,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,0.05);">
                <div>
                    <h1 class="text-lg font-bold text-white">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-500">{{ now()->format('l, F j Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-600">Welcome back,</span>
                    <span class="text-sm font-semibold text-white">{{ Auth::user()->name }}</span>
                </div>
            </header>

            {{-- Flash --}}
            @if(session('success'))
                <div class="mx-8 mt-6 flash-msg">
                    <div class="flex items-center gap-3 p-4 rounded-xl text-sm font-medium"
                        style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #6ee7b7;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            {{-- Content --}}
            <main class="flex-1 px-8 py-8">
                <div class="w-full">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>

</html>