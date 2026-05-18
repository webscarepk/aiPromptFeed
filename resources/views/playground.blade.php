@extends('layouts.app')
@section('page-title', 'API Sandbox & Daily Streak Playground')

@section('content')
    <div x-data="playgroundApp()" x-init="initPlayground()" class="space-y-6">

        {{-- Top Alert / Auth Banner --}}
        <div
            class="card p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-gradient-to-r from-blue-900/20 via-purple-900/10 to-transparent border-blue-500/20">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-500/10 text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">API Authentication state</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Use your current session to mock a Google Sign-In and fetch a
                        Sanctum Bearer Token.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <template x-if="!token">
                    <button @click="authenticateGoogle('{{ Auth::user()->email }}')"
                        class="px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-lg shadow-blue-500/10 hover:shadow-blue-500/20 transition duration-300 transform active:scale-95"
                        style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                        1-Click Google Sign In (Mock)
                    </button>
                </template>
                <template x-if="token">
                    <div class="flex items-center gap-2">
                        <span
                            class="px-3 py-1.5 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-lg">
                            Authenticated
                        </span>
                        <button @click="clearToken()" class="text-xs text-gray-500 hover:text-red-400 transition">Reset
                            Token</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left side: Daily claims and Profile --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- 1. Daily Claim Streak Rewards Panel --}}
                <div class="card p-6 relative overflow-hidden">
                    <div
                        class="absolute -right-16 -top-16 w-32 h-32 bg-purple-500/5 rounded-full blur-2xl pointer-events-none">
                    </div>
                    <div
                        class="absolute -left-16 -bottom-16 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl pointer-events-none">
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span>📅 Daily Claim Streak</span>
                                <span class="text-xs font-normal text-purple-400 px-2 py-0.5 rounded-full bg-purple-500/10">
                                    7-Days Reward
                                </span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Claim daily to maintain your streak and receive larger
                                rewards.</p>
                        </div>

                        <div class="text-right">
                            <span class="text-xs text-gray-500 block">Current Streak</span>
                            <span class="text-xl font-black text-white" x-text="streak.streak_count + ' Days'">0 Days</span>
                        </div>
                    </div>

                    {{-- Horizontal Streak Track --}}
                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-3 mb-6">
                        <template x-for="(reward, index) in streak.streak_rewards_progression" :key="index">
                            <div class="flex flex-col items-center p-3 rounded-xl border text-center transition duration-300 relative group"
                                :class="{
                                     'bg-emerald-500/5 border-emerald-500/30 text-emerald-300': (index < streak.streak_count),
                                     'bg-blue-600/10 border-blue-500/60 ring-2 ring-blue-500/20 text-white scale-105 pulsing-card': (streak.can_claim_today && index == streak.streak_count),
                                     'bg-gray-900 border-gray-800 text-gray-600': (index >= streak.streak_count && !(streak.can_claim_today && index == streak.streak_count))
                                 }">

                                {{-- Checkmark or lock icons --}}
                                <div class="absolute top-1 right-1">
                                    <template x-if="index < streak.streak_count">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template
                                        x-if="index > streak.streak_count || (index == streak.streak_count && !streak.can_claim_today)">
                                        <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </template>
                                </div>

                                <span class="text-[10px] uppercase font-bold tracking-wider block"
                                    :class="index == streak.streak_count ? 'text-blue-400' : 'text-gray-500'">
                                    Day <span x-text="index + 1"></span>
                                </span>
                                <span class="text-lg font-black block my-1" x-text="reward"></span>
                                <span class="text-[9px] uppercase tracking-widest text-gray-600 block">Credits</span>
                            </div>
                        </template>
                    </div>

                    {{-- Action Claim Button --}}
                    <div
                        class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <template x-if="streak.can_claim_today">
                                <p class="text-sm font-semibold text-emerald-400">✨ You are eligible to claim today's
                                    reward!</p>
                            </template>
                            <template x-if="!streak.can_claim_today">
                                <p class="text-sm font-medium text-gray-500">🔒 You have already claimed today's reward.
                                    Next reward available tomorrow.</p>
                            </template>
                            <p class="text-[11px] text-gray-600 mt-0.5"
                                x-text="'Last Claimed At: ' + (streak.last_claimed_at ? formatDateTime(streak.last_claimed_at) : 'Never')">
                            </p>
                        </div>

                        <button @click="claimDailyReward()" :disabled="!streak.can_claim_today || !token"
                            class="px-6 py-2.5 rounded-xl text-sm font-bold text-white shadow-lg transition duration-300 w-full sm:w-auto disabled:opacity-40 disabled:cursor-not-allowed transform active:scale-95 flex items-center justify-center gap-2"
                            :class="streak.can_claim_today && token ? 'bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 shadow-purple-500/10' : 'bg-gray-800 border border-gray-700 text-gray-400'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm-2 4h4M9 16h6" />
                            </svg>
                            Claim Reward (+<span x-text="streak.next_reward_amount">5</span>)
                        </button>
                    </div>
                </div>

                {{-- 2. Edit Profile & Phone Verification --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Edit Profile Form --}}
                    <div class="card p-6">
                        <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Edit Profile API
                        </h3>
                        <form @submit.prevent="updateProfile()" class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Name</label>
                                <input type="text" x-model="profileForm.name"
                                    class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Full
                                    Name</label>
                                <input type="text" x-model="profileForm.full_name"
                                    class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Avatar
                                    URL</label>
                                <input type="text" x-model="profileForm.avatar_url"
                                    class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
                            </div>

                            <button type="submit" :disabled="!token"
                                class="w-full py-2.5 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed border border-gray-700 text-sm font-semibold text-white rounded-xl transition duration-300">
                                Save Profile
                            </button>
                        </form>
                    </div>

                    {{-- Phone Verification Simulation --}}
                    <div class="card p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                Phone Verification System
                            </h3>
                            <p class="text-xs text-gray-500 mb-4">Request a 6-digit OTP code to be sent to your phone (via Twilio if set, or via mock console logs in development mode) and submit the code to verify.</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="phoneForm.phone" placeholder="+923001234567" :disabled="otpSent"
                                            class="flex-grow bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition disabled:opacity-50">
                                        <button @click="sendVerificationCode()" :disabled="!token || !phoneForm.phone || otpSent"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-xs font-bold text-white rounded-xl transition duration-300">
                                            Send Code
                                        </button>
                                    </div>
                                </div>

                                <template x-if="otpSent">
                                    <div class="space-y-2">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">6-Digit Verification Code</label>
                                        <input type="text" x-model="phoneForm.code" placeholder="Enter OTP code"
                                            class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
                                        
                                        <template x-if="mockOtpCode">
                                            <div class="p-2.5 rounded-lg bg-blue-500/10 border border-blue-500/20 text-[11px] text-blue-400 flex items-center justify-between">
                                                <span>💡 Mock Code: <strong x-text="mockOtpCode" class="text-white text-xs select-all"></strong> (Auto-filled!)</span>
                                                <button @click="otpSent = false; mockOtpCode = ''; phoneForm.code = ''" class="text-[10px] text-gray-500 hover:text-red-400">Reset</button>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <div class="p-3 rounded-xl bg-white/[0.01] border border-white/5 flex items-center gap-3">
                                    <template x-if="user.phone_verified_at">
                                        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Verified: <span x-text="user.phone" class="text-white font-bold"></span> at <span x-text="formatDateTime(user.phone_verified_at)"></span>
                                        </div>
                                    </template>
                                    <template x-if="!user.phone_verified_at">
                                        <div class="text-xs text-gray-500 flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                                            Unverified
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <button @click="verifyPhone()" :disabled="!token || !otpSent || !phoneForm.code"
                            class="w-full py-2.5 mt-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-sm font-bold text-white rounded-xl transition duration-300">
                            Submit OTP & Verify
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Side: Credits balances and transaction history logs --}}
            <div class="space-y-6">

                {{-- 1. User Credit Balances per Model --}}
                <div class="card p-6">
                    <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Credit Balances
                    </h3>

                    <div class="space-y-4">
                        <template x-for="balance in credits" :key="balance.model_id">
                            <div class="p-4 rounded-xl bg-gray-900 border border-gray-800">
                                <div class="flex items-center justify-between text-sm font-semibold text-white mb-2">
                                    <span x-text="balance.model_name"></span>
                                    <span x-text="balance.credits_remaining + ' / ' + balance.credits_total + ' Cr'"></span>
                                </div>
                                <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-purple-500 to-blue-500 transition-all duration-500"
                                        :style="'width: ' + ((balance.credits_remaining / (balance.credits_total || 1)) * 100) + '%'">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="credits.length === 0">
                            <div class="text-center py-6 text-xs text-gray-500">
                                No credit balances found. Click Claim Daily Reward or Subscribe to a Plan to get credits!
                            </div>
                        </template>
                    </div>
                </div>

                {{-- 2. Ledger Transaction Log History --}}
                <div class="card p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            Credit Ledger Logs
                        </h3>

                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                            <template x-for="log in history" :key="log.id">
                                <div
                                    class="p-3 rounded-lg bg-white/[0.01] border border-white/5 flex items-start justify-between gap-3 text-xs">
                                    <div>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider"
                                            :class="{
                                                  'bg-emerald-500/10 text-emerald-400': log.type === 'claim' || log.type === 'grant',
                                                  'bg-red-500/10 text-red-400': log.type === 'deduction',
                                                  'bg-purple-500/10 text-purple-400': log.type === 'subscription'
                                              }" x-text="log.type"></span>
                                        <p class="text-gray-300 font-medium mt-1" x-text="log.description"></p>
                                        <span class="text-[10px] text-gray-500"
                                            x-text="formatDateTime(log.created_at)"></span>
                                    </div>
                                    <span class="font-extrabold text-sm flex-shrink-0"
                                        :class="log.amount >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                        x-text="(log.amount >= 0 ? '+' : '') + log.amount"></span>
                                </div>
                            </template>
                            <template x-if="history.length === 0">
                                <div class="text-center py-6 text-xs text-gray-500">
                                    No transactions logged yet.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live Developer API Console --}}
        <div class="card p-6 bg-[#0B0F19] border-gray-800">
            <div class="flex items-center justify-between mb-4 border-b border-gray-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span class="w-3.5 h-3.5 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                        <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    </span>
                    Developer Live API Console
                </h3>
                <button @click="clearConsole()" class="text-xs text-gray-500 hover:text-red-400 transition">Clear
                    Log</button>
            </div>

            <div class="bg-black/40 rounded-xl p-4 font-mono text-xs border border-white/5 space-y-3 h-[250px] overflow-y-auto"
                id="console-output">
                <template x-for="log in consoleLogs" :key="log.timestamp">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-[10px] text-gray-600">
                            <span x-text="log.time"></span>
                            <span x-text="log.type" class="uppercase font-bold tracking-widest"
                                :class="log.type === 'request' ? 'text-blue-400' : (log.status < 300 ? 'text-emerald-400' : 'text-red-400')"></span>
                        </div>
                        <div class="text-gray-300 font-bold" x-text="log.message"></div>
                        <template x-if="log.details">
                            <pre class="bg-black/30 p-2 rounded border border-white/5 text-[11px] text-emerald-300 overflow-x-auto"
                                x-text="JSON.stringify(log.details, null, 4)"></pre>
                        </template>
                    </div>
                </template>
                <template x-if="consoleLogs.length === 0">
                    <div class="text-gray-600 italic text-center py-16">
                        Outgoing requests will log here. Authenticate or claim daily rewards to trigger API calls!
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Confetti Script --}}
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <script>
        function playgroundApp() {
            return {
                token: localStorage.getItem('api_access_token') || '',
                user: {},
                credits: [],
                streak: {
                    streak_count: 0,
                    last_claimed_at: null,
                    can_claim_today: false,
                    next_reward_amount: 5,
                    streak_rewards_progression: [5, 10, 25, 50, 75, 100, 150]
                },
                history: [],
                consoleLogs: [],
                profileForm: { name: '', full_name: '', avatar_url: '' },
                phoneForm: { phone: '', code: '' },
                otpSent: false,
                mockOtpCode: '',

                initPlayground() {
                    if (this.token) {
                        this.fetchUserData();
                        this.fetchCreditHistory();
                    } else {
                        // Try to auto-auth via current user email
                        this.logConsole('info', 'System loaded. Ready for authentication.');
                    }
                },

                clearToken() {
                    localStorage.removeItem('api_access_token');
                    this.token = '';
                    this.user = {};
                    this.credits = [];
                    this.history = [];
                    this.logConsole('info', 'Sanctum Bearer Token cleared.');
                },

                logConsole(type, message, details = null) {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString();
                    this.consoleLogs.unshift({
                        timestamp: now.getTime(),
                        time: timeString,
                        type: type,
                        message: message,
                        details: details
                    });

                    // Keep last 50 logs
                    if (this.consoleLogs.length > 50) this.consoleLogs.pop();
                },

                clearConsole() {
                    this.consoleLogs = [];
                },

                async authenticateGoogle(email) {
                    this.logConsole('request', `POST /api/v1/auth/google -> exchanging Mock token for ${email}`);

                    try {
                        const response = await fetch('/api/v1/auth/google', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ token: email })
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok && data.access_token) {
                            this.token = data.access_token;
                            localStorage.setItem('api_access_token', this.token);
                            this.fetchUserData();
                            this.fetchCreditHistory();
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to authenticate: ${error.message}`);
                    }
                },

                async fetchUserData() {
                    if (!this.token) return;
                    this.logConsole('request', 'GET /api/v1/me -> fetching user details & streak balances');

                    try {
                        const response = await fetch('/api/v1/me', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok) {
                            this.user = data.user;
                            this.credits = data.user.credit_balances || [];
                            this.streak = data.streak;
                            this.profileForm = {
                                name: data.user.name || '',
                                full_name: data.user.full_name || '',
                                avatar_url: data.user.avatar_url || ''
                            };
                            this.phoneForm.phone = data.user.phone || '';
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to fetch user data: ${error.message}`);
                    }
                },

                async claimDailyReward() {
                    if (!this.token) return;
                    this.logConsole('request', 'POST /api/v1/me/credits/claim -> triggering Daily Streak Claim');

                    try {
                        const response = await fetch('/api/v1/me/credits/claim', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok && data.success) {
                            // Success Confetti Effect!
                            confetti({
                                particleCount: 150,
                                spread: 80,
                                origin: { y: 0.6 },
                                colors: ['#3b82f6', '#8b5cf6', '#10b981']
                            });

                            this.fetchUserData();
                            this.fetchCreditHistory();
                        } else {
                            alert(data.message || 'Failed to claim daily reward.');
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to claim credit: ${error.message}`);
                    }
                },

                async fetchCreditHistory() {
                    if (!this.token) return;
                    this.logConsole('request', 'GET /api/v1/me/credits/history -> fetching paginated credit logs');

                    try {
                        const response = await fetch('/api/v1/me/credits/history', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            }
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok && data.history) {
                            this.history = data.history.data || [];
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to fetch credit history: ${error.message}`);
                    }
                },

                async updateProfile() {
                    if (!this.token) return;
                    this.logConsole('request', 'PUT /api/v1/me/profile -> modifying user variables', this.profileForm);

                    try {
                        const response = await fetch('/api/v1/me/profile', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify(this.profileForm)
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok) {
                            this.fetchUserData();
                            alert('Profile updated successfully!');
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to update profile: ${error.message}`);
                    }
                },

                async sendVerificationCode() {
                    if (!this.token || !this.phoneForm.phone) return;
                    this.logConsole('request', 'POST /api/v1/auth/phone/send-otp -> generating and sending OTP code', { phone: this.phoneForm.phone });

                    try {
                        const response = await fetch('/api/v1/auth/phone/send-otp', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`,
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ phone: this.phoneForm.phone })
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok && data.success) {
                            this.otpSent = true;
                            if (data.otp_code) {
                                this.mockOtpCode = data.otp_code;
                                this.phoneForm.code = String(data.otp_code); // auto-fill for frictionless mock testing
                                this.logConsole('info', `Mock verification code loaded: ${data.otp_code}`);
                            }
                            alert(data.message || 'OTP verification code sent!');
                        } else {
                            alert(data.message || 'Failed to send OTP verification code.');
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to send verification code: ${error.message}`);
                    }
                },

                async verifyPhone() {
                    if (!this.token) return;
                    this.logConsole('request', 'POST /api/v1/auth/phone/verify -> saving phone verification state', this.phoneForm);

                    try {
                        const response = await fetch('/api/v1/auth/phone/verify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'Authorization': `Bearer ${this.token}`
                            },
                            body: JSON.stringify(this.phoneForm)
                        });

                        const data = await response.json();
                        this.logConsole('response', `Status ${response.status} ${response.statusText}`, data);

                        if (response.ok) {
                            this.fetchUserData();
                            alert('Phone number verified successfully!');
                            this.otpSent = false;
                            this.mockOtpCode = '';
                            this.phoneForm.code = '';
                        } else {
                            alert(data.message || 'Verification failed.');
                        }
                    } catch (error) {
                        this.logConsole('error', `Failed to verify phone: ${error.message}`);
                    }
                },

                formatDateTime(dateStr) {
                    if (!dateStr) return 'Never';
                    const date = new Date(dateStr);
                    return date.toLocaleString();
                }
            }
        }
    </script>

    <style>
        .pulsing-card {
            animation: pulseBorder 2s infinite alternate;
        }

        @keyframes pulseBorder {
            from {
                box-shadow: 0 0 4px rgba(59, 130, 246, 0.4);
                border-color: rgba(59, 130, 246, 0.5);
            }

            to {
                box-shadow: 0 0 12px rgba(59, 130, 246, 0.8), 0 0 4px rgba(139, 92, 246, 0.4);
                border-color: rgba(59, 130, 246, 1);
            }
        }
    </style>
@endsection