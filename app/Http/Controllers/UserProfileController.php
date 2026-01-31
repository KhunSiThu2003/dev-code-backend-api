<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\userResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        return $this->success(
            ['user' => new userResource($user)],
            'Profile retrieved successfully.'
        );
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $path = $request->file('profile_image')->store('profiles', 'public');
            $validated['profile_image'] = $path;
        }

        $user->update($validated);

        return $this->success(
            ['user' => new userResource($user->fresh())],
            'Profile updated successfully.'
        );
    }

    /**
     * Change the authenticated user's password, revoke all tokens and require login again.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $user->tokens()->delete();

        return $this->success([], 'Password changed successfully. Please log in again with your new password.');
    }

    /**
     * Permanently delete the authenticated user's account and all associated data.
     */
    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        $user = Auth::user();

        $user->tokens()->delete();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->forceDelete();

        return $this->success([], 'Your account has been permanently deleted.');
    }
}
