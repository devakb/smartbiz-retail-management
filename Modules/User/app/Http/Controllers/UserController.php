<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Services\UserService;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('user::index', [
            'title' => 'Users',
            'users' => $this->userService->paginate($search ?: null),
            'search' => $search,
            'stats' => [
                'total' => User::query()->count(),
            ],
            'hasFilters' => $search !== '',
        ]);
    }

    public function create(): View
    {
        return view('user::create', [
            'title' => 'Create user',
            'roles' => $this->userService->roleNames(),
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        AuditLogger::log('user.created', $user, [
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
        ]);

        return ApiResponse::success('User created successfully.', [
            'user' => $this->transform($user),
            'redirect' => route('users.index'),
        ], 201);
    }

    public function edit(User $user): View
    {
        $user->load('roles:id,name');

        return view('user::edit', [
            'title' => 'Edit user',
            'user' => $user,
            'roles' => $this->userService->roleNames(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return ApiResponse::success('User updated successfully.', [
            'user' => $this->transform($user),
            'redirect' => route('users.index'),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->userService->delete($user, $request->user());

        return ApiResponse::success('User deleted successfully.', [
            'redirect' => route('users.index'),
        ]);
    }

    /**
     * @return array{id: int, name: string, email: string, role: string|null}
     */
    protected function transform(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
        ];
    }
}
