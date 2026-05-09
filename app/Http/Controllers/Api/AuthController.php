<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TemporaryUser;
use App\Mail\TempUserVerificationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create a temporary user record
        $verificationToken = Str::random(40);
        $expiresAt = Carbon::now()->addMinutes(60);

        $tempUser = TemporaryUser::create([
            'name' => $request->name,
            'full_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_token' => hash('sha256', $verificationToken),
            'expires_at' => $expiresAt,
        ]);

        // Generate signed verification URL
        $signedUrl = URL::temporarySignedRoute(
            'auth.verify-temp',
            $expiresAt,
            ['id' => $tempUser->id, 'token' => $verificationToken]
        );

        // Send verification email
        Mail::to($tempUser->email)->send(new TempUserVerificationMail($signedUrl));

        return response()->json([
            'message' => 'Registration received. Please check your email to verify your account.',
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

        // Enforce Email Verification
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is not verified. Please check your email for the verification link.',
                'email_verified' => false
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'email_verified' => true
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
        return response()->json([
            'message' => 'Email verified and account created.',
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 200);
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
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function refresh(Request $request)
    {
        // Sanctum doesn't have refresh tokens by default, but we can issue a new one
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
