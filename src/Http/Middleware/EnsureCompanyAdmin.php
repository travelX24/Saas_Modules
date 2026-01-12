<?php

namespace Athka\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();

        if (! $u) {
            return \Illuminate\Support\Facades\Route::has('authkit.login')
                ? redirect()->route('authkit.login')
                : redirect()->route('login');
        }

        // ✅ التحقق من الدومين
        $host = strtolower($request->getHost());
        $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.com'));
        $central = strtolower(env('CENTRAL_DOMAIN', $base));

        // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
        if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
            $ip = $m[1];
            $base = "athkahr.$ip.nip.io";
            $central = $base;
        }

        $isOnCentralDomain = ($host === $central || $host === 'www.'.$central);
        $isOnCompanyDomain = ! $isOnCentralDomain && str_ends_with($host, '.'.$base);

        // ✅ Company Admin - السماح بالدخول على دومين الشركة
        $isCompanyAdmin = $u->hasRole('company-admin') || ! empty($u->saas_company_id);
        if ($isCompanyAdmin && $isOnCompanyDomain) {
            return $next($request);
        }

        // ✅ SaaS Admin على دومين الشركة -> السماح بالدخول (للاستخدام المتزامن)
        $isSaasAdmin = $u->hasAnyRole(['saas-admin', 'system-admin', 'super-admin']) ||
                       (($u->email ?? null) === 'admin@athkahr.com');
        if ($isSaasAdmin && $isOnCompanyDomain) {
            // ✅ إذا كان SaaS Admin على دومين الشركة وكان مرتبط بشركة -> السماح بالدخول
            if (! empty($u->saas_company_id)) {
                return $next($request);
            }
        }

        // ✅ SaaS Admin على الدومين المركزي يحاول فتح route الشركة -> إعادة توجيه لـ SaaS
        if ($isSaasAdmin && $isOnCentralDomain) {
            return redirect()->route('saas.dashboard');
        }

        // ✅ Company Admin على الدومين المركزي -> إعادة توجيه لصفحة الشركة
        if ($isCompanyAdmin && $isOnCentralDomain) {
            return redirect()->route('company-admin.hello');
        }

        abort(403);
    }
}
