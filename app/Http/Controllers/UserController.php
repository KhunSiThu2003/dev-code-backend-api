<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminOrInstructorRequest;
use App\Http\Resources\userResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function store(StoreAdminOrInstructorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'       => Hash::make($validated['password']),
            'role'            => $validated['role'],
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
        ]);

        $message = $validated['role'] === 'admin' ? 'Admin created successfully.' : 'Instructor created successfully.';

        return $this->created(
            ['user' => new userResource($user)],
            $message
        );
    }

    public function getUsers(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $limit   = (int) $request->get('limit');
        $search  = $request->get('search');
        $sortBy  = $request->get('sort_by', 'id');
        $order   = $request->get('order', 'asc');
        $role    = $request->get('role');
        $status  = $request->get('status', 'active');

        $allowedSort = ['id', 'name', 'email', 'created_at', 'deleted_at'];

        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'id';
        }

        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'asc';
        }

        $query = $status === 'deleted' ? User::onlyTrashed() : User::query();

        if ($role && in_array($role, ['admin', 'learner', 'instructor'])) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sortBy, $order);

        if ($limit && $limit > 0) {
            $query->limit($limit);
            $perPage = min($perPage, $limit);
        }

        $users = $query->paginate($perPage);

        if ($users->isEmpty()) {
            $msg = $status === 'deleted' ? 'No deleted users found.' : 'No active users found.';
            return $this->notFound($msg);
        }

        return userResource::collection($users);
    }

    public function getActiveUsers(Request $request)
    {
        $request->merge(['status' => 'active']);

        return $this->getUsers($request);
    }

    public function getDeletedUsers(Request $request)
    {
        $request->merge(['status' => 'deleted']);

        return $this->getUsers($request);
    }

    public function getAdmins(Request $request)
    {
        $request->merge(['role' => 'admin']);
        $request->merge(['status' => 'active']);

        return $this->getUsers($request);
    }

    public function getInstructors(Request $request)
    {
        $request->merge(['role' => 'instructor']);
        $request->merge(['status' => 'active']);

        return $this->getUsers($request);
    }

    public function getLearners(Request $request)
    {
        $request->merge(['role' => 'learner']);
        $request->merge(['status' => 'active']);

        return $this->getUsers($request);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success(
            ['user' => new userResource($user)],
            'User retrieved successfully.'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $user->tokens()->delete();
        $user->delete();

        return $this->success([], 'User has been deleted.');
    }

    public function restore(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        if (!$user->trashed()) {
            return $this->error('User is not deleted.', 422);
        }

        $user->restore();

        return $this->success(
            ['user' => new userResource($user->fresh())],
            'User has been restored.'
        );
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $user->tokens()->delete();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->forceDelete();

        return $this->success([], 'User has been permanently deleted.');
    }
}
