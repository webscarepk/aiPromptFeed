<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — AiPromptFeed</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glow-blue { box-shadow: 0 0 60px rgba(59,130,246,0.15); }
        .bg-mesh {
            background-color: #030712;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(59,130,246,0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(139,92,246,0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 60% 80%, rgba(16,185,129,0.05) 0%, transparent 50%);
        }
        .input-field {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            transition: all 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(59,130,246,0.7);
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
            background: rgba(255,255,255,0.07);
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
            box-shadow: 0 8px 30px rgba(59,130,246,0.4);
            transform: translateY(-1px);
        }
        .btn-primary:active { transform: translateY(0); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
        .float { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-mesh min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-10 float">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 mb-5 shadow-2xl shadow-blue-900/40">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Recover Account</h1>
            <p class="text-gray-500 mt-1 text-sm">We'll email you a reset link</p>
        </div>

        {{-- Card --}}
        <div class="bg-gray-900/80 backdrop-blur-xl border border-white/10 rounded-3xl p-8 glow-blue">

            <div class="mb-6 text-sm text-gray-400 leading-relaxed text-center">
                {{ __('Forgot your password? No problem. Enter your email and we\'ll send you a link to reset it.') }}
            </div>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="mb-5 p-3 bg-blue-900/40 border border-blue-500/30 text-blue-400 text-sm rounded-xl text-center">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                {{-- Email Address --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               class="input-field w-full pl-11 pr-4 py-3.5 rounded-xl text-sm"
                               placeholder="you@example.com" required autofocus autocomplete="username">
                    </div>
                    @error('email')
                        <p class="mt-2 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3.5 rounded-xl text-white font-semibold text-sm tracking-wide">
                    Email Password Reset Link
                </button>

                <div class="text-center pt-2">
                    <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-white transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Login
                    </a>
                </div>
            </form>
        </div>

        <p class="text-center text-xs text-gray-700 mt-8">&copy; {{ date('Y') }} AiPromptFeed. All rights reserved.</p>
    </div>
</body>
</html>

