<?php

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Modules\Profile\Http\Requests\UpdatePasswordRequest;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Services\ProfileService;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService) {}

    public function edit(): View
    {
        $user = auth()->user();
        $loginRecords = $this->profileService->loginRecords($user, 10);

        return view('profile::edit', [
            'title' => 'Profile',
            'user' => $user,
            'loginRecords' => $loginRecords,
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success('Profile updated successfully.', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->profileService->updatePassword(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success('Password updated successfully.');
    }

    public function loginRecords(): JsonResponse
    {
        $records = $this->profileService->loginRecords(auth()->user());

        return ApiResponse::success('Login records retrieved.', [
            'records' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
        ]);
    }
}
