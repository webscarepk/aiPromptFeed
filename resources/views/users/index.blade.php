@extends('layouts.app')
@section('page-title', 'Users Management')

@section('content')
<div x-data="userModal()">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Users</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $users->total() }} users total</p>
    </div>
</div>

{{-- Search --}}
<form method="GET" action="{{ route('users.index') }}" class="flex gap-3 mb-6">
    <div class="relative flex-grow max-w-sm">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <input type="text" name="search" value="{{ request('search') }}"
            class="input-search w-full pl-11 pr-4 py-2.5 text-sm" placeholder="Search by name or email...">
    </div>
    <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white rounded-xl transition"
        style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">Search</button>
    @if(request('search'))
        <a href="{{ route('users.index') }}"
            class="px-5 py-2.5 text-sm font-medium text-red-400 rounded-xl transition"
            style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">✕ Clear</a>
    @endif
</form>

<div class="card overflow-hidden">
    <table class="w-full text-left">
        <thead style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.06);">
            <tr>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Plan</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Credits Remaining</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="transition hover:bg-white/[0.02]" style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex flex-shrink-0 items-center justify-center font-bold text-white"
                                style="background: linear-gradient(135deg, #2563eb, #7c3aed);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->subscriptionPlan)
                            <span class="px-3 py-1 text-xs font-medium bg-purple-500/10 text-purple-400 rounded-full border border-purple-500/20">
                                {{ $user->subscriptionPlan->name }}
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-medium bg-gray-500/10 text-gray-400 rounded-full border border-gray-500/20">
                                No Plan
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($user->creditBalances->isEmpty())
                            <span class="text-xs text-gray-500">No credits available</span>
                        @else
                            <div class="space-y-1">
                                @foreach($user->creditBalances as $balance)
                                    <div class="text-xs">
                                        <span class="font-medium text-gray-300">{{ $balance->aiModel->name ?? 'Unknown Model' }}:</span> 
                                        @if($balance->credits_remaining > 0)
                                            <span class="text-green-400">{{ $balance->credits_remaining }}</span>
                                        @else
                                            <span class="text-red-400">{{ $balance->credits_remaining }}</span>
                                        @endif
                                        <span class="text-gray-500">/ {{ $balance->credits_total }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($user->id !== 1 && $user->id !== auth()->id())
                        <button @click="openEdit({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', {{ $user->subscription_id ?? 'null' }})" class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-400/10 rounded-lg transition duration-200" title="Edit User">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" title="Delete User"
                                class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-16 text-center text-gray-600">
                        <div class="text-4xl mb-3">👥</div>No users found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($users->hasPages())
    <div class="mt-6 flex items-center justify-between">
        <p class="text-sm text-gray-500">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</p>
        <div class="flex items-center gap-1">
            @if($users->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-600 cursor-not-allowed">← Prev</span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">← Prev</a>
            @endif
            
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition">Next →</a>
            @else
                <span class="px-3 py-2 text-sm text-gray-600 cursor-not-allowed">Next →</span>
            @endif
        </div>
    </div>
@endif

    <!-- Modal -->
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4" style="display:none;" @keydown.escape.window="open = false">
        <div class="w-full max-w-lg bg-gray-900 border border-gray-700 rounded-xl max-h-[90vh] overflow-y-auto">
            <div class="p-5 border-b border-gray-800 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white">Edit User</h3>
                <button @click="open = false" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            
            <form :action="'/users/' + editId" method="POST" class="p-5 space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Name</label>
                    <input type="text" name="name" x-model="form.name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" x-model="form.email" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Subscription Plan</label>
                    <select name="subscription_id" x-model="form.subscription_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white">
                        <option value="">No Plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->price_usd_cents }}¢)</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-800">
                    <button type="button" @click="open = false" class="px-4 py-2 text-gray-400 hover:text-white mr-3">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function userModal() {
        return {
            open: false, editId: null,
            form: { name: '', email: '', subscription_id: '' },
            openEdit(id, name, email, subscriptionId) {
                this.editId = id;
                this.form = { name: name, email: email, subscription_id: subscriptionId || '' };
                this.open = true;
            }
        }
    }
</script>
@endsection
