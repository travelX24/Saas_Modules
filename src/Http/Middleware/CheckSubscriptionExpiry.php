<?php

namespace Athka\Saas\Http\Middleware;

use Athka\Saas\Models\SaasCompanyOtherinfo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckSubscriptionExpiry
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // ✅ فقط للمستخدمين المرتبطين بشركة
        if (! $user->saas_company_id) {
            return $next($request);
        }

        // Cache لمدة 1 دقيقة
        $cacheKey = "company:subscription:{$user->saas_company_id}";
        $settings = Cache::remember($cacheKey, now()->addMinute(), function () use ($user) {
            return SaasCompanyOtherinfo::where('company_id', $user->saas_company_id)->first();
        });

        if (! $settings || ! $settings->subscription_ends_at) {
            return $next($request);
        }

        // ✅ التحقق من انتهاء الاشتراك
        if ($settings->subscription_ends_at->isPast()) {
            // ✅ حفظ الرسالة أولاً قبل invalidate
            $errorMessage = tr('Your subscription has expired. Please contact system administration to renew your subscription.');

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // ✅ حفظ الرسالة بعد regenerateToken مباشرة
            $request->session()->flash('error', $errorMessage);

            $loginRoute = \Illuminate\Support\Facades\Route::has('authkit.login')
                ? route('authkit.login')
                : (\Illuminate\Support\Facades\Route::has('login') ? route('login') : '/login');

            return redirect($loginRoute);
        }

        return $next($request);
    }
}
