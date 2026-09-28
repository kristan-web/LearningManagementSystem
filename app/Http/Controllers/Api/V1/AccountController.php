<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    /* ================================================================
     *  OTP — email a 6-digit code via Brevo, then verify it
     * ============================================================== */
    public function sendOtp(Request $request): JsonResponse
    {
        $email = strtolower($request->validate(['email' => ['required', 'email', 'max:100']])['email']);
        $generic = ['success' => true, 'message' => 'If an account exists for that email, a code has been sent.'];

        // Only mail known accounts, but answer the same either way (no email enumeration).
        if (! User::where('email', $email)->exists()) {
            return response()->json($generic);
        }

        $code = (string) random_int(100000, 999999);
        Cache::put("otp:{$email}", ['hash' => Hash::make($code), 'attempts' => 0], now()->addMinutes(10));

        if (! self::mailOtp($email, $code, 'Enter this code to verify your email.', route('otp', ['email' => $email]))) {
            Cache::forget("otp:{$email}");

            return response()->json([
                'success' => false,
                'errors'  => ['We could not send the code right now. Please try again later.'],
            ], 502);
        }

        return response()->json($generic);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'code'  => ['required', 'digits:6'],
        ]);
        $error = self::checkOtp('otp:' . strtolower($data['email']), $data['code']);

        if ($error) {
            return response()->json(['success' => false, 'reason' => $error === self::OTP_EXPIRED ? 'expired' : 'invalid',
                'errors' => [$error]], 422);
        }

        // One-time ticket that lets this email set a new password on the reset page.
        $token = Str::random(64);
        Cache::put("otp-reset:{$token}", strtolower($data['email']), now()->addMinutes(15));

        return response()->json(['success' => true, 'message' => 'Email verified.', 'reset_token' => $token]);
    }

    public function resetPasswordWithOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'    => ['required', 'string', 'size:64'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);

        $email = Cache::pull("otp-reset:{$data['token']}");
        $user = $email ? User::where('email', $email)->first() : null;

        if (! $user) {
            return response()->json(['success' => false, 'reason' => 'expired',
                'errors' => ['This reset session has expired. Please verify your email again.']], 422);
        }

        $user->update(['password' => $data['password']]); // 'hashed' cast hashes it

        return response()->json(['success' => true, 'message' => 'Your password has been reset. You can now log in.']);
    }

    public const OTP_EXPIRED = 'This code has expired. Please request a new one.';

    /** Email a 6-digit OTP via Brevo. Returns false when the send fails. */
    public static function mailOtp(string $email, string $code, string $intro, ?string $link = null): bool
    {
        $sent = Http::withHeaders(['api-key' => config('services.brevo.key')])
            ->acceptJson()
            ->post('https://api.brevo.com/v3/smtp/email', [
                'sender'      => ['email' => config('services.brevo.sender_email'), 'name' => config('services.brevo.sender_name')],
                'to'          => [['email' => $email]],
                'subject'     => 'Your verification code',
                'htmlContent' => view('components.otp.email', compact('code', 'intro', 'link'))->render(),
            ]);

        if ($sent->failed()) {
            Log::error('Brevo OTP send failed', ['status' => $sent->status(), 'body' => $sent->body()]);
        }

        return $sent->successful();
    }

    /** Check a cached OTP. Returns an error message, or null when the code is valid (and consumed). */
    public static function checkOtp(string $key, string $code): ?string
    {
        $otp = Cache::get($key);

        if (! $otp) {
            return self::OTP_EXPIRED;
        }

        if (! Hash::check($code, $otp['hash'])) {
            // 5 wrong tries burns the code.
            if (++$otp['attempts'] >= 5) {
                Cache::forget($key);
            } else {
                Cache::put($key, $otp, now()->addMinutes(10));
            }

            return 'That code is incorrect.';
        }

        Cache::forget($key);

        return null;
    }
}
