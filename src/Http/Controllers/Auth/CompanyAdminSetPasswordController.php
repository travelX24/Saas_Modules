<?php

namespace Athka\Saas\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Athka\Saas\Models\SaasCompany;
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

        $companyDomain = null;

        $status = Password::broker()->reset(
            $request->only('email', 'token', 'password', 'password_confirmation'),
            function ($user, $password) use (&$companyDomain) {
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

                // ✅ احصل على دومين الشركة
                if (!empty($user->saas_company_id)) {
                    $company = SaasCompany::find($user->saas_company_id);
                    if ($company && !empty($company->primary_domain)) {
                        $companyDomain = $company->primary_domain;
                    }
                }

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // ✅ احفظ دومين الشركة في session للاستخدام في صفحة done
            if ($companyDomain) {
                $request->session()->flash('company_domain', $companyDomain);
            }

            return redirect()->route('saas.company-admin.password.done');
        }

        // ✅ فحص إذا كان الـ token منتهي أو غير صالح
        if ($status === Password::INVALID_TOKEN || $status === Password::INVALID_USER) {
            // ✅ عرض toast message بدلاً من رسالة تحت الحقل
            $errorMessage = function_exists('tr') 
                ? tr('This password reset link has expired. Please contact the administration to resend the password setup email.') 
                : 'This password reset link has expired. Please contact the administration to resend the password setup email.';
            
            return back()
                ->with('error', $errorMessage)
                ->withInput();
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->withInput();
    }

    public function done(Request $request)
    {
        // ✅ احصل على دومين الشركة من session
        $companyDomain = $request->session()->get('company_domain');
        
        // ✅ بناء رابط login على دومين الشركة
        $loginUrl = '/login';
        if ($companyDomain) {
            $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.com'));
            $scheme = $request->isSecure() ? 'https' : 'http';
            $port = $request->getPort();
            $portPart = in_array($port, [80, 443], true) ? '' : ':'.$port;
            
            // ✅ لو نحن على nip.io استخرج IP
            if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $request->getHost(), $m)) {
                $ip = $m[1];
                $base = "athkahr.$ip.nip.io";
            }
            
            $loginUrl = $scheme.'://'.$companyDomain.'.'.$base.$portPart.'/login';
        }

        return view('saas::auth.company-admin-set-password-done', [
            'loginUrl' => $loginUrl,
        ]);
    }
}
