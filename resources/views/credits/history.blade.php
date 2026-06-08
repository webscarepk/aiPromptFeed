@extends('layouts.app')
@section('page-title', 'Credit History')

@section('content')
<div x-data="creditHistoryApp()" x-init="init()" class="space-y-6">

    {{-- ===== HERO BALANCE CARD ===== --}}
    <div class="relative overflow-hidden rounded-2xl p-8 text-center"
         style="background: linear-gradient(135deg, #1a0a00 0%, #2d1500 40%, #1a0a00 100%);
                border: 1px solid rgba(249,115,22,0.25);">

        {{-- Glow blobs --}}
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-32 rounded-full blur-3xl opacity-30"
                 style="background: radial-gradient(circle, #f97316 0%, transparent 70%);"></div>
        </div>

        {{-- Sparkle icon --}}
        <div class="relative mx-auto mb-4 w-20 h-20 rounded-2xl flex items-center justify-center shadow-2xl"
             style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 0 40px rgba(249,115,22,0.4);">
            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5L12 2z"/>
                <path d="M5 5L3.5 8.5 0 10l3.5 1.5L5 15l1.5-3.5L10 10 6.5 8.5 5 5z" opacity="0.6"/>
                <path d="M19 1l-1 2.5L15.5 5l2.5 1 1 2.5 1-2.5L22.5 5 20 4l-1-3z" opacity="0.6"/>
            </svg>
        </div>

        {{-- Current Balance --}}
        <div class="relative">
            <p class="text-6xl font-black text-white tracking-tight" style="text-shadow: 0 0 30px rgba(249,115,22,0.6);">
                {{ number_format($currentBalance) }}
            </p>
            <p class="text-sm text-orange-400/70 mt-1 font-medium uppercase tracking-widest">Current Balance</p>
        </div>

        {{-- Earned / Spent stats --}}
        <div class="relative grid grid-cols-2 gap-4 mt-6 max-w-xs mx-auto">
            <div class="p-4 rounded-xl text-left" style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);">
                <div class="flex items-center gap-1.5 mb-1">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span class="text-xl font-black text-emerald-400">+{{ number_format($totalEarned) }}</span>
                </div>
                <p class="text-xs text-emerald-700 font-medium">Total Earned</p>
            </div>
            <div class="p-4 rounded-xl text-left" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">
                <div class="flex items-center gap-1.5 mb-1">
                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    <span class="text-xl font-black text-red-400">-{{ number_format($totalSpent) }}</span>
                </div>
                <p class="text-xs text-red-700 font-medium">Total Spent</p>
            </div>
        </div>
    </div>

    {{-- ===== WAYS TO EARN ===== --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="text-orange-400">⚡</span> Ways to Earn
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Complete tasks to earn more credits</p>
            </div>
            <span class="text-xs text-orange-400 font-bold bg-orange-500/10 border border-orange-500/20 px-3 py-1 rounded-full">
                {{ collect($waysToEarn)->where('claimed', false)->count() }} available
            </span>
        </div>

        <div class="space-y-3">
            @foreach($waysToEarn as $way)
            <div class="flex items-center gap-4 p-4 rounded-xl transition-all duration-200
                        {{ $way['claimed'] ? 'opacity-50' : 'hover:bg-white/5' }}"
                 style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06);">

                {{-- Icon --}}
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                     style="background: rgba(255,255,255,0.04);">
                    @if($way['icon'] === 'email') 📧
                    @elseif($way['icon'] === 'phone') 📱
                    @elseif($way['icon'] === 'profile') 👤
                    @elseif($way['icon'] === 'streak') 🔥
                    @elseif($way['icon'] === 'ad') 🎬
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-grow min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-white">{{ $way['title'] }}</p>
                        @if($way['frequency'] === 'daily')
                        <span class="text-[9px] uppercase font-bold tracking-wider text-blue-400 bg-blue-500/10 px-1.5 py-0.5 rounded-full">Daily</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $way['description'] }}</p>

                    {{-- Ad progress indicator --}}
                    @if($way['icon'] === 'ad' && !$way['claimed'])
                    <div class="flex items-center gap-2 mt-1.5">
                        @for($i = 0; $i < $way['daily_limit']; $i++)
                        <div class="h-1 w-8 rounded-full {{ $i < $way['today_count'] ? 'bg-orange-400' : 'bg-gray-700' }}"></div>
                        @endfor
                        <span class="text-[10px] text-gray-500">{{ $way['today_count'] }}/{{ $way['daily_limit'] }} today</span>
                    </div>
                    @endif
                </div>

                {{-- Action / Badge --}}
                @if($way['claimed'])
                <span class="flex items-center gap-1 text-xs text-emerald-400 font-semibold flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Claimed
                </span>
                @else
                <button @click="claimWay('{{ $way['icon'] }}')"
                        class="flex-shrink-0 px-3 py-1.5 rounded-xl text-xs font-black text-white transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-lg"
                        style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 4px 15px rgba(249,115,22,0.3);">
                    {{ $way['reward'] }}
                </button>
                @endif
            </div>
            @endforeach
        </div>

        {{-- AdMob Note --}}
        <p class="mt-4 text-[10px] text-gray-700 text-center">
            Powered by AdMob · Ad Unit: {{ env('ADMOB_REWARDED_AD_UNIT_ID', 'ca-app-pub-3174635776582237/7910987056') }}
        </p>
    </div>

    {{-- ===== TRANSACTION HISTORY ===== --}}
    <div class="card p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Transaction History
            </h3>
            <span class="text-xs text-gray-600">{{ $history->total() }} records</span>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex gap-2 mb-5">
            @foreach(['all' => 'All', 'earned' => 'Earned', 'spent' => 'Spent'] as $key => $label)
            <a href="{{ route('credits.history', ['filter' => $key]) }}"
               class="px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200
                      {{ $filter === $key
                         ? 'text-white shadow-lg'
                         : 'text-gray-500 hover:text-gray-300 hover:bg-white/5' }}"
               @if($filter === $key)
               style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 4px 15px rgba(249,115,22,0.25);"
               @endif>
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Transaction List --}}
        <div class="space-y-3">
            @forelse($history as $entry)
            <div class="flex items-center gap-4 p-4 rounded-xl transition-all duration-200 hover:bg-white/5"
                 style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04);">

                {{-- Type icon --}}
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-base"
                     style="background: rgba(255,255,255,0.04);">
                    @if($entry->type === 'claim') 🔥
                    @elseif($entry->type === 'ad_reward') 🎬
                    @elseif($entry->type === 'deduction') ⚡
                    @elseif($entry->type === 'refund') 🔄
                    @elseif($entry->type === 'subscription') 💎
                    @elseif($entry->type === 'grant') 🎁
                    @else ⭐
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-grow min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[9px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full
                            @if(in_array($entry->type, ['claim','grant','ad_reward','refund'])) bg-emerald-500/10 text-emerald-400
                            @elseif($entry->type === 'deduction') bg-red-500/10 text-red-400
                            @elseif($entry->type === 'subscription') bg-purple-500/10 text-purple-400
                            @else bg-blue-500/10 text-blue-400
                            @endif">
                            {{ $entry->type === 'ad_reward' ? 'Ad Reward' : ucfirst($entry->type) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-300 font-medium mt-1 truncate">{{ $entry->description }}</p>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs text-gray-600">{{ $entry->created_at->diffForHumans() }}</span>
                        @if($entry->balance_after !== null)
                        <span class="text-xs text-gray-700">Balance: {{ number_format($entry->balance_after) }}</span>
                        @endif
                    </div>
                </div>

                {{-- Amount --}}
                <div class="flex-shrink-0 text-right">
                    <span class="text-lg font-black {{ $entry->amount > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $entry->amount > 0 ? '+' : '' }}{{ number_format($entry->amount) }}
                    </span>
                    @if($entry->balance_before !== null)
                    <p class="text-[10px] text-gray-700 mt-0.5">
                        was {{ number_format($entry->balance_before) }}
                    </p>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-16">
                <div class="text-5xl mb-4">📭</div>
                <p class="text-gray-500 font-medium">No transactions yet</p>
                <p class="text-xs text-gray-600 mt-1">Claim your daily streak or watch an ad to get started!</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($history->hasPages())
        <div class="mt-6 flex justify-center">
            <div class="flex items-center gap-2">
                @if($history->onFirstPage())
                <span class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-800 cursor-not-allowed">← Prev</span>
                @else
                <a href="{{ $history->appends(['filter' => $filter])->previousPageUrl() }}"
                   class="px-3 py-1.5 text-xs text-gray-400 hover:text-white rounded-lg border border-gray-800 hover:border-gray-600 transition">← Prev</a>
                @endif

                <span class="text-xs text-gray-500 px-2">
                    Page {{ $history->currentPage() }} of {{ $history->lastPage() }}
                </span>

                @if($history->hasMorePages())
                <a href="{{ $history->appends(['filter' => $filter])->nextPageUrl() }}"
                   class="px-3 py-1.5 text-xs text-gray-400 hover:text-white rounded-lg border border-gray-800 hover:border-gray-600 transition">Next →</a>
                @else
                <span class="px-3 py-1.5 text-xs text-gray-600 rounded-lg border border-gray-800 cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

{{-- Watch Ad Modal --}}
<div x-show="showAdModal" x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showAdModal=false"></div>
    <div class="relative w-full max-w-sm rounded-2xl p-6 text-center"
         style="background: linear-gradient(135deg, #1a0a00, #2d1500); border: 1px solid rgba(249,115,22,0.3); box-shadow: 0 25px 60px rgba(0,0,0,0.8);">

        {{-- Ad simulation --}}
        <div class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center text-3xl"
             style="background: linear-gradient(135deg, #f97316, #ea580c);">
            🎬
        </div>
        <h3 class="text-lg font-bold text-white mb-1">Watch Ad</h3>
        <p class="text-sm text-gray-400 mb-6">Earn <span class="text-orange-400 font-bold">+10 credits</span> by watching a short rewarded ad</p>

        {{-- Progress bar (simulated countdown) --}}
        <div class="mb-5">
            <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000"
                     style="background: linear-gradient(90deg, #f97316, #ea580c);"
                     :style="'width: ' + adProgress + '%'"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2" x-text="adCountdown > 0 ? 'Ad ends in ' + adCountdown + 's...' : 'Ad complete!'"></p>
        </div>

        {{-- Action button --}}
        <button @click="claimAdReward()"
                :disabled="adCountdown > 0 || adLoading"
                class="w-full py-3 rounded-xl text-sm font-bold text-white transition-all duration-200 transform active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                style="background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 8px 25px rgba(249,115,22,0.3);">
            <span x-show="adLoading">Claiming...</span>
            <span x-show="!adLoading && adCountdown > 0">Please wait...</span>
            <span x-show="!adLoading && adCountdown === 0">Claim +10 Credits</span>
        </button>

        <button @click="showAdModal=false; clearInterval(adTimer)"
                class="mt-3 text-xs text-gray-600 hover:text-gray-400 transition">Cancel</button>

        <p class="mt-4 text-[9px] text-gray-700">
            AdMob · {{ env('ADMOB_REWARDED_AD_UNIT_ID', 'ca-app-pub-3174635776582237/7910987056') }}
        </p>
    </div>
</div>

{{-- Toast notification --}}
<div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl max-w-sm"
     :style="toast.type === 'success'
             ? 'background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);'
             : 'background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3);'"
     style="display:none;">
    <span x-text="toast.type === 'success' ? '✅' : '❌'"></span>
    <span class="text-sm font-semibold text-white" x-text="toast.message"></span>
</div>

<script>
function creditHistoryApp() {
    return {
        showAdModal: false,
        adCountdown: 5,
        adProgress: 0,
        adTimer: null,
        adLoading: false,
        toast: { show: false, message: '', type: 'success' },

        token: localStorage.getItem('api_access_token') || '',

        init() {
            // Nothing special on load
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3500);
        },

        claimWay(icon) {
            if (icon === 'ad') {
                this.openAdModal();
            } else if (icon === 'streak') {
                window.location.href = '/playground';
            } else if (icon === 'phone') {
                window.location.href = '/playground';
            } else if (icon === 'profile') {
                window.location.href = '/playground';
            }
        },

        openAdModal() {
            this.showAdModal = true;
            this.adCountdown = 5;
            this.adProgress = 0;
            this.adLoading = false;

            // Simulate ad countdown
            this.adTimer = setInterval(() => {
                this.adCountdown--;
                this.adProgress = ((5 - this.adCountdown) / 5) * 100;
                if (this.adCountdown <= 0) {
                    this.adProgress = 100;
                    clearInterval(this.adTimer);
                }
            }, 1000);
        },

        async claimAdReward() {
            if (!this.token) {
                this.showToast('Please authenticate first via the API Playground.', 'error');
                return;
            }

            this.adLoading = true;
            try {
                const response = await fetch('/api/v1/me/credits/watch-ad', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.showAdModal = false;
                    this.showToast(data.message || '+10 credits earned!', 'success');
                    // Reload after short delay to refresh balances
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.showAdModal = false;
                    this.showToast(data.message || 'Failed to claim ad reward.', 'error');
                }
            } catch (err) {
                this.showToast('Network error: ' + err.message, 'error');
            } finally {
                this.adLoading = false;
            }
        }
    };
}
</script>

<style>
.card { background: #111827; border: 1px solid #1f2937; border-radius: 16px; }
</style>
@endsection
