<?php

namespace Athka\Saas\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CompanyAdminSetPasswordController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
        ]);

        return view('saas::auth.company-admin-set-password', [
            'email' => $request->query('email'),
            'token' => $request->query('token'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => function_exists('tr') ? tr('The passwords do not match.') : 'The passwords do not match.',
            'password.min' => function_exists('tr') ? tr('The password must be at least :min characters.') : 'The password must be at least :min characters.',
            'password.required' => function_exists('tr') ? tr('The password field is required.') : 'The password field is required.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);

                // بما أنه وصل للرابط من بريده = نعتبره Verified (اختياري)
                if (Schema::hasColumn('users', 'email_verified_at') && empty($user->email_verified_at)) {
                    $user->email_verified_at = now();
                }

                // لو عندك حقل مثل must_change_password
                if (Schema::hasColumn('users', 'must_change_password')) {
                    $user->must_change_password = false;
                }

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('saas.company-admin.password.done');
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->withInput();
    }

    public function done()
    {
        return view('saas::auth.company-admin-set-password-done');
    }
}
