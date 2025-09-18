<?php

namespace App\Http\Controllers\Auth;

use App\Models\Tenant\TenantUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class TenantPasswordResetController extends Controller
{
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.tenant-setinitial-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::broker('tenant_users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (TenantUser $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('filament.app.auth.login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
