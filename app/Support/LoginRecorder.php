<?php

namespace App\Support;

use App\Models\LoginRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LoginRecorder
{
    public static function record(Request $request, string $status, ?User $user = null): void
    {
        if (! Schema::hasTable('login_records')) {
            return;
        }

        LoginRecord::query()->create([
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'status' => $status,
            'session_id' => $status === 'success' ? $request->session()->getId() : null,
            'logged_in_at' => now(),
        ]);
    }
}
