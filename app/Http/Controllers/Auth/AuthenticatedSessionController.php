<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\ApiResponse;
use App\Support\LoginRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.signin', [
            'title' => 'Sign In',
        ]);
    }

    public function store(LoginRequest $request): JsonResponse|RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        $user->forceFill([
            'session_version' => (int) $user->session_version + 1,
        ])->save();

        $request->session()->put('session_version', (int) $user->session_version);

        $this->invalidateOtherSessions($user->id, $request->session()->getId());

        LoginRecorder::record($request, 'success', $user);

        $redirect = route('dashboard');

        if ($request->expectsJson() || $request->ajax()) {
            return ApiResponse::success('Signed in successfully.', [
                'redirect' => $redirect,
            ]);
        }

        return redirect()->intended($redirect);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function invalidateOtherSessions(int $userId, string $currentSessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}
