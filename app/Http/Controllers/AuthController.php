<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Resources\userResource;
use App\Mail\ForgotPasswordOtpMail;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function userRegister(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'learner',
        ]);

        if ($error = $this->canSendOtp($user)) {
            return $this->locked($error['message'], [
                'remaining_minutes' => $error['remaining_minutes'],
                'remaining_seconds' => $error['remaining_seconds'],
            ]);
        }
        $this->sendOtp($user);

        return $this->created(
            ['user' => new userResource($user)],
            'User registered successfully. OTP has been sent.'
        );
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFound('User not found.');
        }

        if($user->otp_verified_at) {
            return $this->error('User already verified.', 401, ['user_verified' => true]);
        }

        $this->checkAndResetExpiredLocks($user);

        if ($this->isOtpLocked($user)) {
            $remaining = $this->getLockRemainingTime($user);
            return $this->locked(
                "Account locked due to too many attempts. Try again in {$remaining['minutes']} minutes {$remaining['seconds']} seconds.",
                [
                    'remaining_minutes' => $remaining['minutes'],
                    'remaining_seconds' => $remaining['seconds'],
                ]
            );
        }

        if ($user->otp_code !== $request->otp_code) {
            $user->increment('otp_attempts');
            if ($user->fresh()->otp_attempts >= 5) {
                $user->update(['otp_locked_at' => now()]);
                return $this->locked('Account locked for 10 minutes due to too many failed attempts.', [
                    'remaining_minutes' => 10,
                    'remaining_seconds' => 0,
                ]);
            }
            return $this->error('Invalid OTP.', 401, ['otp_attempts' => $user->otp_attempts]);
        }

        if (!$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return $this->error('OTP has expired. Please request a new one.', 401, ['otp_expired' => true]);
        }

        $this->clearOtp($user);

        return $this->success(
            ['user' => new userResource($user)],
            'OTP verified successfully.'
        );
    }

    public function userLogin(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->unauthorized('Invalid credentials.');
        }

        $user = Auth::user();

        if (!$user->otp_verified_at) {
            return $this->error('Please verify your OTP first.', 401, ['user_verified' => false]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success(
            [
                'token' => $token,
                'user'  => new userResource($user),
            ],
            'Login successful.'
        );
    }

    public function userLogout(): JsonResponse
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = Auth::user()->currentAccessToken();
        $token->delete();

        return $this->success([], 'Logged out successfully.');
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFound('User not found.');
        }

        $this->checkAndResetExpiredLocks($user);

        if ($error = $this->canSendOtp($user)) {
            return $this->locked($error['message'], [
                'remaining_minutes' => $error['remaining_minutes'],
                'remaining_seconds' => $error['remaining_seconds'],
            ]);
        }
        $this->sendForgotPasswordOtp($user);

        return $this->success([
            'otp_request_count' => $user->otp_request_count
        ], 'OTP has been sent to your email.');
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFound('User not found.');
        }

        $this->checkAndResetExpiredLocks($user);

        if($user->otp_verified_at) {
            return $this->error('User already verified.', 401, ['user_verified' => true]);
        }

        if ($error = $this->canSendOtp($user)) {
            return $this->locked($error['message'], [
                'remaining_minutes' => $error['remaining_minutes'],
                'remaining_seconds' => $error['remaining_seconds'],
            ]);
        }

        $type = $request->input('type', 'registration');
        if ($type === 'forgot_password') {
            $this->sendForgotPasswordOtp($user);
        } else {
            $this->sendOtp($user);
        }

        return $this->success([
            'otp_request_count' => $user->otp_request_count
        ], 'OTP has been sent to your email.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFound('User not found.');
        }

        $this->checkAndResetExpiredLocks($user);

        if ($this->isOtpLocked($user)) {
            $remaining = $this->getLockRemainingTime($user);
            return $this->locked(
                "Account locked due to too many attempts. Try again in {$remaining['minutes']} minutes {$remaining['seconds']} seconds.",
                [
                    'remaining_minutes' => $remaining['minutes'],
                    'remaining_seconds' => $remaining['seconds'],
                ]
            );
        }

        if (!$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return $this->unauthorized('OTP has expired. Please request a new one.');
        }

        if ($user->otp_code !== $request->otp_code) {
            $user->increment('otp_attempts');
            if ($user->fresh()->otp_attempts >= 5) {
                $user->update(['otp_locked_at' => now()]);
                return $this->locked('Account locked for 10 minutes due to too many failed attempts.', [
                    'remaining_minutes' => 10,
                    'remaining_seconds' => 0,
                ]);
            }
            return $this->error('Invalid OTP.', 401, ['otp_attempts' => $user->otp_attempts]);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $this->clearOtp($user);
        $user->tokens()->delete();

        return $this->success(
            ['user' => new userResource($user)],
            'Password reset successfully. Please log in again with your new password.'
        );
    }

    protected function sendOtp(User $user): void
    {
        $otp = random_int(100000, 999999);
        $requestCount = $user->otp_request_count + 1;

        $user->update([
            'otp_verified_at'        => null,
            'otp_code'               => $otp,
            'otp_expires_at'         => now()->addMinutes(10),
            'otp_attempts'           => 0,
            'otp_request_count'      => $requestCount,
            'otp_last_sent_at'       => now(),
            'otp_locked_at'          => null,
            'otp_request_locked_at'  => $requestCount >= 5 ? now() : null,
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));
    }

    protected function sendForgotPasswordOtp(User $user): void
    {
        $otp = random_int(100000, 999999);
        $requestCount = $user->otp_request_count + 1;

        $user->update([
            'otp_verified_at'        => null,
            'otp_code'               => $otp,
            'otp_expires_at'         => now()->addMinutes(10),
            'otp_attempts'           => 0,
            'otp_request_count'      => $requestCount,
            'otp_last_sent_at'       => now(),
            'otp_locked_at'          => null,
            'otp_request_locked_at'  => $requestCount >= 5 ? now() : null,
        ]);

        Mail::to($user->email)->send(new ForgotPasswordOtpMail($otp));
    }

    protected function canSendOtp(User $user): ?array
    {
        if ($user->otp_request_locked_at) {
            $lockExpiresAt = $user->otp_request_locked_at->copy()->addMinutes(10);
            if ($lockExpiresAt->isFuture()) {
                $remaining = $this->getOtpRequestLockRemainingTime($user);
                return [
                    'message' => "Too many OTP requests. Try again in {$remaining['minutes']} minutes {$remaining['seconds']} seconds.",
                    'remaining_minutes' => $remaining['minutes'],
                    'remaining_seconds' => $remaining['seconds'],
                ];
            } else {
                $user->update([
                    'otp_request_count'       => 0,
                    'otp_request_locked_at'   => null,
                ]);
            }
        }

        return null;
    }

    protected function isOtpLocked(User $user): bool
    {
        if (!$user->otp_locked_at) {
            return false;
        }
        return $user->otp_locked_at->copy()->addMinutes(10)->isFuture();
    }

    protected function getLockRemainingTime(User $user): array
    {
        if (!$user->otp_locked_at) {
            return ['minutes' => 0, 'seconds' => 0];
        }
        $expiresAt = $user->otp_locked_at->copy()->addMinutes(10);
        if ($expiresAt->isPast()) {
            return ['minutes' => 0, 'seconds' => 0];
        }
        $totalSeconds = (int) now()->diffInSeconds($expiresAt);
        return [
            'minutes' => (int) floor($totalSeconds / 60),
            'seconds' => $totalSeconds % 60,
        ];
    }

    protected function getOtpRequestLockRemainingTime(User $user): array
    {
        if (!$user->otp_request_locked_at) {
            return ['minutes' => 0, 'seconds' => 0];
        }
        $expiresAt = $user->otp_request_locked_at->copy()->addMinutes(10);
        if ($expiresAt->isPast()) {
            return ['minutes' => 0, 'seconds' => 0];
        }
        $totalSeconds = (int) now()->diffInSeconds($expiresAt);
        return [
            'minutes' => (int) floor($totalSeconds / 60),
            'seconds' => $totalSeconds % 60,
        ];
    }

    protected function clearOtp(User $user): void
    {
        $user->update([
            'otp_code'               => null,
            'otp_expires_at'         => null,
            'otp_attempts'           => 0,
            'otp_request_count'      => 0,
            'otp_last_sent_at'       => null,
            'otp_locked_at'          => null,
            'otp_request_locked_at'  => null,
            'otp_verified_at'        => now(),
        ]);
    }

    protected function checkAndResetExpiredLocks(User $user): void
    {
        if ($user->otp_locked_at) {
            $lockExpiresAt = $user->otp_locked_at->copy()->addMinutes(10);
            if ($lockExpiresAt->isPast()) {
                $user->update([
                    'otp_attempts' => 0,
                    'otp_locked_at' => null,
                ]);
            }
        }

        if ($user->otp_request_locked_at) {
            $lockExpiresAt = $user->otp_request_locked_at->copy()->addMinutes(10);
            if ($lockExpiresAt->isPast()) {
                $user->update([
                    'otp_request_count' => 0,
                    'otp_request_locked_at' => null,
                ]);
            }
        }
    }

}