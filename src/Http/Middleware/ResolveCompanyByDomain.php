<?php

namespace Athka\Saas\Http\Middleware;

use Athka\Saas\Models\SaasCompany;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResolveCompanyByDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());

        $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.com')); // athkahr.com أو athkahr.lvh.me
        $central = strtolower(env('CENTRAL_DOMAIN', $base));          // نفس base غالباً

        // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
        if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
            $ip = $m[1];
            $base = "athkahr.$ip.nip.io";
            $central = $base;
        }
        // نفس base غالباً

        // ✅ الدومين المركزي (login + صفحات عامة) ليس شركة
        if ($host === $central || $host === 'www.'.$central) {
            return $next($request);
        }

        // ✅ لازم يكون شكل: {tenant}.{base}
        $suffix = '.'.$base;
        if (! str_ends_with($host, $suffix)) {
            abort(404);
        }

        // خذ {tenant} قبل .base
        $tenantKey = substr($host, 0, -strlen($suffix));
        $tenantKey = explode('.', $tenantKey)[0];

        // Cache لمدة 1 دقيقة
        $cacheKey = "company:domain:{$tenantKey}";
        $company = Cache::remember($cacheKey, now()->addMinute(), function () use ($tenantKey) {
            return SaasCompany::with('settings')->where('primary_domain', $tenantKey)->first();
        });

        if (! $company) {
            // بدلاً من 404، وجهه للدومين الرئيسي ليعيد تسجيل الدخول
            return redirect()->away('https://' . $central);
        }

        app()->instance('currentCompany', $company);

        return $next($request);
    }
}
