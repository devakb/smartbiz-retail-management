<?php

namespace Modules\Profile\Services;

use App\Models\LoginRecord;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ProfileService
{
    /**
     * @param  array{name: string, email: string}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ])->save();

        return $user->refresh();
    }

    /**
     * @param  array{password: string}  $data
     */
    public function updatePassword(User $user, array $data): void
    {
        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();
    }

    public function loginRecords(User $user, int $perPage = 15): LengthAwarePaginator
    {
        if (! Schema::hasTable('login_records')) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return LoginRecord::query()
            ->where('user_id', $user->id)
            ->orderByDesc('logged_in_at')
            ->paginate($perPage);
    }
}
