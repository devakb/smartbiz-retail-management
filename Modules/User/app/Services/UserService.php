<?php

namespace Modules\User\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with('roles:id,name')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{name: string, email: string, password: string, role: string}  $data
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
                'session_version' => 0,
            ]);

            $user->syncRoles([$this->resolveRole($data['role'])]);

            return $user->load('roles:id,name');
        });
    }

    /**
     * @param  array{name: string, email: string, password?: string|null, role: string}  $data
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->fill($payload)->save();
            $user->syncRoles([$this->resolveRole($data['role'])]);

            return $user->refresh()->load('roles:id,name');
        });
    }

    public function delete(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        $user->delete();
    }

    /**
     * @return list<string>
     */
    public function roleNames(): array
    {
        return Role::query()->orderBy('name')->pluck('name')->all();
    }

    protected function resolveRole(string $roleName): Role
    {
        $role = Role::query()->where('name', $roleName)->first();

        if (! $role) {
            throw ValidationException::withMessages([
                'role' => 'The selected role is invalid.',
            ]);
        }

        return $role;
    }
}
