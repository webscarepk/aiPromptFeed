<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TemporaryUser;
use App\Models\CreditHistory;
use App\Models\AiModel;
use App\Models\UserCreditBalance;
use App\Mail\TempUserVerificationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the user directly (bypassing email verification/temporary users as requested)
        $user = User::create([
            'name' => $request->name,
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'email_verified_at' => now(), // DO NOT auto-verify, they will verify later from the app
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        $accessToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // We no longer block login for unverified emails ("user register without 2fa")
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'email_verified' => $user->hasVerifiedEmail()
        ]);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        // Existing verification for already‑created users (kept for backward compatibility)
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return response('<html><body style="font-family:sans-serif;text-align:center;padding:50px;"><h2>Email Verified Successfully!</h2><p>You can now return to the app and login.</p></body></html>', 200)
            ->header('Content-Type', 'text/html');
    }

    // New verification for temporary users
    public function verifyTempEmail(Request $request, $id, $token)
    {
        $temp = TemporaryUser::findOrFail($id);

        if ($temp->verification_token !== hash('sha256', $token) || $temp->expires_at->isPast()) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 403);
        }

        // Promote temporary user to permanent user
        $user = User::create([
            'name' => $temp->name,
            'full_name' => $temp->full_name,
            'email' => $temp->email,
            'password' => $temp->password, // already hashed
            'email_verified_at' => now(),
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));
        $temp->delete();

        $accessToken = $user->createToken('auth_token')->plainTextToken;

        // Grant +10 credits for Email Verification
        $creditGranted = $this->grantBonusCredits($user, 'Verify Email', 'Verify Email bonus — +10 credits awarded.');

        // Check if requesting JSON response or HTML redirect
        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'message' => 'Email verified and account created.',
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'user' => $user,
                'credit_granted' => $creditGranted,
            ], 200);
        }

        // Get app redirect URL from query param or config
        $redirectUrl = $request->query('redirect_to', config('app.frontend_url', 'http://localhost:3000'));
        $redirectUrl = rtrim($redirectUrl, '/');

        // Determine redirect destination - could be login with token or dashboard
        $redirectUrl .= '/auth/verify?token=' . urlencode($accessToken);

        // Return HTML that redirects to app with token
        return response('<html><body style="font-family:sans-serif;text-align:center;padding:50px;">
            <h2>Email Verified Successfully!</h2>
            <p>Redirecting you to the app...</p>
            <script>
                // Store token in localStorage for the app to access
                localStorage.setItem("auth_token", "' . $accessToken . '");
                localStorage.setItem("user", JSON.stringify(' . json_encode($user) . '));
                // Redirect to app
                window.location.href = "' . $redirectUrl . '";
            </script>
            <p>If you are not redirected, <a href="' . $redirectUrl . '">click here</a></p>
        </body></html>', 200)
            ->header('Content-Type', 'text/html');
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check for a temporary user first
        $temp = TemporaryUser::where('email', $request->email)->first();
        if ($temp) {
            // Regenerate token and expiry
            $newToken = Str::random(40);
            $temp->verification_token = hash('sha256', $newToken);
            $temp->expires_at = Carbon::now()->addMinutes(60);
            $temp->save();

            $signedUrl = URL::temporarySignedRoute(
                'auth.verify-temp',
                $temp->expires_at,
                ['id' => $temp->id, 'token' => $newToken]
            );
            Mail::to($temp->email)->send(new TempUserVerificationMail($signedUrl));
            return response()->json(['message' => 'Verification link resent.']);
        }

        // Fallback to existing verified users
        $user = User::where('email', $request->email)->first();
        if ($user && $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }
        if ($user) {
            $user->sendEmailVerificationNotification();
            return response()->json(['message' => 'Verification link sent!']);
        }
        return response()->json(['message' => 'User not found.'], 404);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['creditBalances.aiModel']);

        $canClaim = true;
        $streakCount = $user->streak_count;
        $lastClaimed = $user->last_claimed_at;

        if ($lastClaimed) {
            $lastClaimedDate = Carbon::parse($lastClaimed)->startOfDay();
            $todayDate = Carbon::now()->startOfDay();

            if ($lastClaimedDate->equalTo($todayDate)) {
                $canClaim = false;
            } else {
                $yesterdayDate = Carbon::yesterday()->startOfDay();
                if ($lastClaimedDate->lessThan($yesterdayDate)) {
                    $streakCount = 0;
                }
            }
        }

        $rewards = [5, 10, 25, 50, 75, 100, 150];
        if ($canClaim) {
            $nextStreakIndex = $streakCount % 7;
        } else {
            $nextStreakIndex = ($streakCount - 1) % 7;
            if ($nextStreakIndex < 0)
                $nextStreakIndex = 6;
        }
        $nextRewardAmount = $rewards[$nextStreakIndex];

        // Credit summary totals
        $totalEarned = \App\Models\CreditHistory::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');
        $totalSpent = \App\Models\CreditHistory::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount');

        // Today's watch-ad count
        $todayAdCount = (int) \Illuminate\Support\Facades\Cache::get('watch_ad_count_' . $user->id . '_' . now()->toDateString(), 0);

        return response()->json([
            'user'   => $user,
            'streak' => [
                'streak_count'               => $user->streak_count,
                'last_claimed_at'            => $user->last_claimed_at,
                'can_claim_today'            => $canClaim,
                'next_reward_amount'         => $nextRewardAmount,
                'streak_rewards_progression' => $rewards,
            ],
            'credit_summary' => [
                'total_earned'    => (int) $totalEarned,
                'total_spent'     => (int) $totalSpent,
                'today_ad_views'  => $todayAdCount,
                'daily_ad_limit'  => 3,
                'can_watch_ad'    => $todayAdCount < 3,
            ],
        ]);
    }

    public function googleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->token;

        // Local testing bypass
        if (app()->environment('local', 'testing') && !str_starts_with($token, 'ey')) {
            $email = $token;
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $name = ucwords(str_replace(['.', '_'], ' ', explode('@', $email)[0]));
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'full_name' => $name,
                        'email_verified_at' => now(),
                        'password' => Hash::make(Str::random(16)),
                    ]
                );

                $creditGranted = $this->grantBonusCredits($user, 'Verify Email', 'Verify Email bonus — +10 credits awarded.');
                $accessToken = $user->createToken('auth_token')->plainTextToken;
                
                return response()->json([
                    'access_token' => $accessToken,
                    'token_type' => 'Bearer',
                    'user' => $user,
                    'credit_granted' => $creditGranted,
                    'credit_bonus' => $creditGranted ? 10 : 0,
                ]);
            }
            return response()->json(['message' => 'Invalid local mock token format.'], 400);
        }

        try {
            $client = new \Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($token);

            if (!$payload || !isset($payload['email'])) {
                return response()->json(['message' => 'Invalid or expired Google token.'], 400);
            }

            $email = $payload['email'];
            $name = $payload['name'] ?? ($payload['given_name'] ?? 'Google User');
            $avatarUrl = $payload['picture'] ?? null;

            $user = User::where('email', $email)->first();

            if ($user) {
                if (empty($user->avatar_url) && !empty($avatarUrl)) {
                    $user->avatar_url = $avatarUrl;
                    $user->save();
                }
                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }
            } else {
                $user = User::create([
                    'name' => $name,
                    'full_name' => $name,
                    'email' => $email,
                    'avatar_url' => $avatarUrl,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)),
                ]);
            }

            // Grant +10 credits for Email Verification
            $creditGranted = $this->grantBonusCredits($user, 'Verify Email', 'Verify Email bonus — +10 credits awarded.');

            $accessToken = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'user' => $user,
                'credit_granted' => $creditGranted,
                'credit_bonus' => $creditGranted ? 10 : 0,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'recaptcha_token' => 'nullable|string',
        ]);

        $phone = $request->phone;
        $firebaseApiKey = env('FIREBASE_API_KEY') ?? env('FIREBASE_WEB_API_KEY');

        // Flow 1: If Firebase API Key is configured, use Firebase REST API to send SMS!
        if ($firebaseApiKey) {
            try {
                $payload = [
                    'phoneNumber' => $phone,
                ];
                if ($request->filled('recaptcha_token')) {
                    $payload['recaptchaToken'] = $request->recaptcha_token;
                }

                $response = \Illuminate\Support\Facades\Http::post(
                    "https://identitytoolkit.googleapis.com/v1/accounts:sendVerificationCode?key={$firebaseApiKey}",
                    $payload
                );

                if ($response->successful()) {
                    $data = $response->json();
                    $sessionInfo = $data['sessionInfo'] ?? null;

                    if ($sessionInfo) {
                        // Store the sessionInfo in Cache mapped to the phone number for verification later
                        \Illuminate\Support\Facades\Cache::put('firebase_session_' . $phone, $sessionInfo, now()->addMinutes(10));

                        return response()->json([
                            'success' => true,
                            'message' => 'Verification code sent via Firebase successfully.',
                            'firebase_mode' => true,
                            'session_info' => $sessionInfo
                        ]);
                    }
                } else {
                    $errorMessage = $response->json()['error']['message'] ?? 'Firebase API request failed.';
                    \Illuminate\Support\Facades\Log::error("Firebase SMS send failed for phone {$phone}: " . $response->body());
                    return response()->json([
                        'success' => false,
                        'message' => 'Firebase failed to send SMS: ' . $errorMessage,
                    ], 400);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Firebase SMS exception for phone {$phone}: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Firebase SMS gateway error: ' . $e->getMessage(),
                ], 500);
            }
        }

        // Flow 2: Default to Twilio SMS or Mock local developer fallback
        $otp = rand(100000, 999999);

        // Store OTP in Cache for 10 minutes
        \Illuminate\Support\Facades\Cache::put('phone_otp_' . $phone, $otp, now()->addMinutes(10));

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('TWILIO_NUMBER');
        $smsSent = false;
        $errorMessage = null;

        if ($twilioSid && $twilioToken && $twilioNumber) {
            try {
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($twilioSid, $twilioToken)
                    ->asForm()
                    ->post("https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json", [
                        'To' => $phone,
                        'From' => $twilioNumber,
                        'Body' => "Your AiPromptFeed verification code is: {$otp}"
                    ]);

                if ($response->successful()) {
                    $smsSent = true;
                } else {
                    $errorMessage = $response->json()['message'] ?? 'Twilio API request failed.';
                    \Illuminate\Support\Facades\Log::error("Twilio SMS send failed for phone {$phone}: " . $response->body());
                }
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                \Illuminate\Support\Facades\Log::error("SMS verification exception for phone {$phone}: " . $e->getMessage());
            }
        }

        $returnData = [
            'success' => true,
            'message' => $smsSent ? 'Verification code sent to your phone.' : 'Verification code generated (Mock Mode).',
            'sms_sent' => $smsSent
        ];

        if (!$smsSent || app()->environment('local', 'testing')) {
            $returnData['otp_code'] = $otp; // Show OTP directly for frictionless testing
            if ($errorMessage) {
                $returnData['error_details'] = $errorMessage;
            }
        }

        return response()->json($returnData);
    }

    public function verifyPhone(Request $request)
    {
        // Option A: If request contains client-side validated JWT Firebase token
        if ($request->has('token')) {
            $request->validate([
                'token' => 'required|string',
            ]);

            $token = $request->token;
            $parts = explode('.', $token);
            $phone = null;

            if (count($parts) !== 3) {
                // Local dev / mock fallback: if token is a direct phone number, mock verify it for frictionless testing!
                if (app()->environment('local', 'testing') || !str_starts_with($token, 'ey')) {
                    $phone = $token;
                    if (empty($phone)) {
                        return response()->json(['success' => false, 'message' => 'Invalid phone number format.'], 400);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'Invalid Firebase token format.'], 400);
                }
            } else {
                try {
                    // Decode Firebase Phone Auth JWT ID Token payload (second segment)
                    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                    if (!$payload || (!isset($payload['phone_number']) && !isset($payload['firebase']['identities']['phone'][0]))) {
                        return response()->json(['success' => false, 'message' => 'Invalid token payload or missing verified phone number.'], 400);
                    }

                    $phone = $payload['phone_number'] ?? $payload['firebase']['identities']['phone'][0];
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Failed to parse Firebase ID token: ' . $e->getMessage()], 400);
                }
            }
        } else {
            // Option B: Backend-driven REST verification using phone and code
            $request->validate([
                'phone' => 'required|string|max:20',
                'code' => 'required|string|max:10',
            ]);

            $phone = $request->phone;
            $code = $request->code;

            $firebaseApiKey = env('FIREBASE_API_KEY') ?? env('FIREBASE_WEB_API_KEY');
            $sessionInfo = \Illuminate\Support\Facades\Cache::get('firebase_session_' . $phone);

            if ($firebaseApiKey && $sessionInfo) {
                try {
                    $response = \Illuminate\Support\Facades\Http::post(
                        "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPhoneNumber?key={$firebaseApiKey}",
                        [
                            'sessionInfo' => $sessionInfo,
                            'code' => $code
                        ]
                    );

                    if ($response->successful()) {
                        \Illuminate\Support\Facades\Cache::forget('firebase_session_' . $phone);
                        // User verified!
                    } else {
                        $errorMessage = $response->json()['error']['message'] ?? 'Firebase OTP verification failed.';
                        return response()->json(['success' => false, 'message' => 'Firebase verification failed: ' . $errorMessage], 400);
                    }
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Firebase exception: ' . $e->getMessage()], 500);
                }
            } else {
                // Fallback to local cache verification (if twilio or local mock mode is used)
                $cachedOtp = \Illuminate\Support\Facades\Cache::get('phone_otp_' . $phone);

                if (!$cachedOtp || (string) $cachedOtp !== (string) $code) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired verification code.'
                    ], 400);
                }

                \Illuminate\Support\Facades\Cache::forget('phone_otp_' . $phone);
            }
        }

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'Could not retrieve verified phone number.'], 400);
        }

        // Save verification to database
        $user = $request->user();
        $user->phone = $phone;
        $user->phone_verified_at = now();
        $user->save();

        // Grant +10 credits for Phone Verification
        $creditGranted = $this->grantBonusCredits($user, 'Verify Phone', 'Verify Phone bonus — +10 credits awarded.');

        return response()->json([
            'success' => true,
            'message' => 'Phone number verified via Firebase successfully.',
            'user' => $user,
            'credit_granted' => $creditGranted,
            'credit_bonus' => $creditGranted ? 10 : 0,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'       => 'nullable|string|max:255',
            'full_name'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'avatar_url' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();

        // Check BEFORE save if profile was already complete
        $wasComplete = !empty($user->full_name) && !empty($user->avatar_url);

        if ($request->has('name'))       $user->name       = $request->name;
        if ($request->has('full_name'))  $user->full_name  = $request->full_name;
        if ($request->has('phone'))      $user->phone      = $request->phone;
        if ($request->has('avatar_url')) $user->avatar_url = $request->avatar_url;

        $user->save();

        $creditGranted = false;

        // Grant +10 credits when profile is completed for the FIRST time
        $isNowComplete = !empty($user->full_name) && !empty($user->avatar_url);
        
        if (!$wasComplete && $isNowComplete) {
            $creditGranted = $this->grantBonusCredits($user, 'Complete Profile', 'Complete Profile bonus — +10 credits awarded.');
        }

        return response()->json([
            'message'        => 'Profile updated successfully.',
            'user'           => $user,
            'credit_granted' => $creditGranted,
            'credit_bonus'   => $creditGranted ? 10 : 0,
        ]);
    }

    /**
     * Helper to grant 10 bonus credits across all models for specific one-time actions.
     */
    protected function grantBonusCredits($user, $actionKey, $description)
    {
        $alreadyGranted = CreditHistory::where('user_id', $user->id)
            ->where('type', 'grant')
            ->where('description', 'LIKE', '%' . $actionKey . '%')
            ->exists();

        if ($alreadyGranted) {
            return false;
        }

        try {
            DB::beginTransaction();

            $activeModels = AiModel::where('is_active', true)->get();
            if ($activeModels->isEmpty()) {
                $activeModels = AiModel::all();
            }

            $balanceBefore = 0;
            $balanceAfter  = 0;

            foreach ($activeModels as $model) {
                $balance = UserCreditBalance::where('user_id', $user->id)
                    ->where('model_id', $model->id)
                    ->first();

                if ($balance) {
                    $balanceBefore = $balance->credits_remaining;
                    $balance->credits_remaining += 10;
                    $balance->credits_total     += 10;
                    $balance->save();
                    $balanceAfter = $balance->credits_remaining;
                } else {
                    UserCreditBalance::create([
                        'user_id'           => $user->id,
                        'model_id'          => $model->id,
                        'credits_remaining' => 10,
                        'credits_total'     => 10,
                    ]);
                    $balanceBefore = 0;
                    $balanceAfter  = 10;
                }
            }

            CreditHistory::create([
                'user_id'        => $user->id,
                'amount'         => 10,
                'type'           => 'grant',
                'description'    => $description,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function claimCredits(Request $request)
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $now = Carbon::now();
            $today = Carbon::now()->startOfDay();
            $yesterday = Carbon::yesterday()->startOfDay();

            if ($user->last_claimed_at) {
                $lastClaimedDate = Carbon::parse($user->last_claimed_at)->startOfDay();

                if ($lastClaimedDate->equalTo($today)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already claimed your daily reward today. Please try again tomorrow.',
                    ], 400);
                }

                if ($lastClaimedDate->equalTo($yesterday)) {
                    $newStreak = ($user->streak_count % 7) + 1;
                } else {
                    $newStreak = 1;
                }
            } else {
                $newStreak = 1;
            }

            $rewards = [
                1 => 5,
                2 => 10,
                3 => 25,
                4 => 50,
                5 => 75,
                6 => 100,
                7 => 150,
            ];

            $rewardAmount = $rewards[$newStreak] ?? 5;

            $user->streak_count    = $newStreak;
            $user->last_claimed_at = $now;
            $user->save();

            $activeModels = AiModel::where('is_active', true)->get();
            if ($activeModels->isEmpty()) {
                $activeModels = AiModel::all();
            }

            $balanceBefore = 0;
            $balanceAfter  = 0;

            foreach ($activeModels as $model) {
                $balance = UserCreditBalance::where('user_id', $user->id)
                    ->where('model_id', $model->id)
                    ->first();

                if ($balance) {
                    $balanceBefore = $balance->credits_remaining;
                    $balance->credits_remaining += $rewardAmount;
                    $balance->credits_total     += $rewardAmount;
                    $balance->save();
                    $balanceAfter = $balance->credits_remaining;
                } else {
                    UserCreditBalance::create([
                        'user_id'           => $user->id,
                        'model_id'          => $model->id,
                        'credits_remaining' => $rewardAmount,
                        'credits_total'     => $rewardAmount,
                    ]);
                    $balanceBefore = 0;
                    $balanceAfter  = $rewardAmount;
                }
            }

            CreditHistory::create([
                'user_id'        => $user->id,
                'amount'         => $rewardAmount,
                'type'           => 'claim',
                'description'    => "Claimed Day {$newStreak} streak reward of {$rewardAmount} credits.",
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully claimed Day {$newStreak} reward of {$rewardAmount} credits!",
                'reward_amount' => $rewardAmount,
                'streak_count' => $newStreak,
                'credits' => $user->creditBalances()->with('aiModel:id,name,slug')->get()->map(function ($balance) {
                    return [
                        'model_id' => $balance->model_id,
                        'model_name' => $balance->aiModel->name ?? 'Unknown',
                        'credits_remaining' => $balance->credits_remaining,
                        'credits_total' => $balance->credits_total,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to claim daily reward: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function creditHistory(Request $request)
    {
        $user = $request->user();

        // Total earned / spent
        $totalEarned = CreditHistory::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalSpent = abs(CreditHistory::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        // Current overall balance
        $currentBalance = UserCreditBalance::where('user_id', $user->id)
            ->sum('credits_remaining');

        // Today's ad view count
        $todayAdCount = (int) \Illuminate\Support\Facades\Cache::get(
            'watch_ad_count_' . $user->id . '_' . now()->toDateString(), 0
        );
        $query = CreditHistory::where('user_id', $user->id)->latest();

        // Filter by type: earned (positive), spent (negative/deduction/refund)
        $filter = $request->query('filter', 'all');
        if ($filter === 'earned') {
            $query->where('amount', '>', 0);
        } elseif ($filter === 'spent') {
            $query->where('amount', '<', 0);
        }

        $history = $query->paginate(20);

        // "Ways to Earn" task status
        $waysToEarn = [
            [
                'icon'        => 'email',
                'title'       => 'Verify Email',
                'reward'      => '+10',
                'frequency'   => 'once',
                'claimed'     => CreditHistory::where('user_id', $user->id)
                    ->where('type', 'grant')
                    ->where('description', 'LIKE', '%Verify Email%')
                    ->exists(),
                'description' => 'Verify your email address',
            ],
            [
                'icon'        => 'phone',
                'title'       => 'Verify Phone',
                'reward'      => '+10',
                'frequency'   => 'once',
                'claimed'     => CreditHistory::where('user_id', $user->id)
                    ->where('type', 'grant')
                    ->where('description', 'LIKE', '%Verify Phone%')
                    ->exists(),
                'description' => 'Verify your phone number',
            ],
            [
                'icon'        => 'profile',
                'title'       => 'Complete Profile',
                'reward'      => '+10',
                'frequency'   => 'once',
                'claimed'     => CreditHistory::where('user_id', $user->id)
                    ->where('type', 'grant')
                    ->where('description', 'LIKE', '%Complete Profile%')
                    ->exists(),
                'description' => 'Fill in your profile details',
            ],
            [
                'icon'        => 'streak',
                'title'       => 'Daily Streak',
                'reward'      => '+5-150/day',
                'frequency'   => 'daily',
                'claimed'     => false,
                'description' => 'Claim your daily streak reward',
            ],
            [
                'icon'        => 'ad',
                'title'       => 'Watch Ad',
                'reward'      => '+10',
                'frequency'   => 'daily',
                'claimed'     => $todayAdCount >= 3,
                'today_count' => $todayAdCount,
                'daily_limit' => 3,
                'can_watch'   => $todayAdCount < 3,
                'description' => 'Watch a short ad to earn credits',
                'admob_unit'  => env('ADMOB_REWARDED_AD_UNIT_ID', 'ca-app-pub-3174635776582237/7910987056'),
            ],
        ];

        return response()->json([
            'success'         => true,
            'total_earned'    => (int) $totalEarned,
            'total_spent'     => (int) $totalSpent,
            'current_balance' => (int) $currentBalance,
            'ways_to_earn'    => $waysToEarn,
            'history'         => $history,
        ]);
    }

    /**
     * Watch Ad to earn +10 credits (daily limit: 3 times per day).
     * Called by mobile after a successful AdMob rewarded ad view.
     * App ID: ca-app-pub-3174635776582237~9732020944
     */
    public function watchAd(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();
        $cacheKey = 'watch_ad_count_' . $user->id . '_' . $today;
        $adRewardAmount = 10; // +10 credits per ad view
        $dailyLimit = 3;      // max 3 ads per day

        // Check today's ad view count
        $todayCount = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

        if ($todayCount >= $dailyLimit) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the daily limit of ' . $dailyLimit . ' ads. Come back tomorrow!',
                'today_ad_views' => $todayCount,
                'daily_limit'    => $dailyLimit,
            ], 429);
        }

        try {
            DB::beginTransaction();

            // Add credits to all active models (same as daily streak)
            $activeModels = \App\Models\AiModel::where('is_active', true)->get();
            if ($activeModels->isEmpty()) {
                $activeModels = \App\Models\AiModel::all();
            }

            $balanceBefore = 0;
            $balanceAfter  = 0;

            foreach ($activeModels as $model) {
                $balance = UserCreditBalance::where('user_id', $user->id)
                    ->where('model_id', $model->id)
                    ->first();

                if ($balance) {
                    $balanceBefore = $balance->credits_remaining;
                    $balance->credits_remaining += $adRewardAmount;
                    $balance->credits_total      += $adRewardAmount;
                    $balance->save();
                    $balanceAfter = $balance->credits_remaining;
                } else {
                    UserCreditBalance::create([
                        'user_id'           => $user->id,
                        'model_id'          => $model->id,
                        'credits_remaining' => $adRewardAmount,
                        'credits_total'     => $adRewardAmount,
                    ]);
                    $balanceBefore = 0;
                    $balanceAfter  = $adRewardAmount;
                }
            }

            // Log to credit history
            CreditHistory::create([
                'user_id'        => $user->id,
                'amount'         => $adRewardAmount,
                'type'           => 'ad_reward',
                'description'    => 'Earned ' . $adRewardAmount . ' credits by watching an ad.',
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
            ]);

            DB::commit();

            // Increment daily ad counter (expires at midnight)
            $secondsUntilMidnight = now()->endOfDay()->diffInSeconds(now());
            \Illuminate\Support\Facades\Cache::put($cacheKey, $todayCount + 1, $secondsUntilMidnight);

            return response()->json([
                'success'        => true,
                'message'        => "You earned +{$adRewardAmount} credits for watching an ad!",
                'reward_amount'  => $adRewardAmount,
                'today_ad_views' => $todayCount + 1,
                'daily_limit'    => $dailyLimit,
                'can_watch_ad'   => ($todayCount + 1) < $dailyLimit,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process ad reward: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Called by the mobile app after the user successfully verifies their email
     * (e.g. via Firebase or a custom OTP flow on the client side).
     * This marks the email as verified and grants the +10 credits reward.
     */
    public function verifyEmailReward(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.',
            ], 400);
        }

        // Mark as verified
        $user->markEmailAsVerified();

        // Grant reward
        $creditGranted = $this->grantBonusCredits($user, 'Verify Email', 'Email verified from mobile app — +10 credits awarded.');

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'credit_granted' => $creditGranted,
            'credit_bonus' => $creditGranted ? 10 : 0,
            'user' => $user
        ]);
    }

    /**
     * Called by the mobile app after the user successfully verifies their phone number
     * locally via Firebase or an OTP SDK.
     * This marks the phone as verified and grants the +10 credits reward.
     */
    public function verifyPhoneReward(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'phone' => 'nullable|string'
        ]);

        if ($user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Phone is already verified.',
            ], 400);
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        $user->phone_verified_at = now();
        $user->save();

        // Grant reward
        $creditGranted = $this->grantBonusCredits($user, 'Verify Phone', 'Phone verified from mobile app — +10 credits awarded.');

        return response()->json([
            'success' => true,
            'message' => 'Phone verified successfully!',
            'credit_granted' => $creditGranted,
            'credit_bonus' => $creditGranted ? 10 : 0,
            'user' => $user
        ]);
    }

    /**
     * Called by the mobile app when the user completes their profile.
     */
    public function completeProfileReward(Request $request)
    {
        $user = $request->user();

        // Grant reward
        $creditGranted = $this->grantBonusCredits($user, 'Complete Profile', 'Complete Profile bonus — +10 credits awarded.');

        if (!$creditGranted) {
            return response()->json([
                'success' => false,
                'message' => 'Profile reward already claimed.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully!',
            'credit_granted' => $creditGranted,
            'credit_bonus' => 10,
            'user' => $user
        ]);
    }
}
