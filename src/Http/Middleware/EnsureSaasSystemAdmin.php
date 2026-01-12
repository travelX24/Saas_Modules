<?php

namespace Athka\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSaasSystemAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();

        if (! $u) {
            return \Illuminate\Support\Facades\Route::has('authkit.login')
                ? redirect()->route('authkit.login')
                : redirect()->route('login');
        }

        // ✅ التحقق من الدومين أولاً
        $host = strtolower($request->getHost());
        $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.com'));
        $central = strtolower(env('CENTRAL_DOMAIN', $base));

        // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
        if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
            $ip = $m[1];
            $base = "athkahr.$ip.nip.io";
            $central = $base;
        }

        // ✅ إذا كان على الدومين المركزي (SaaS)
        $isOnCentralDomain = ($host === $central || $host === 'www.'.$central);

        // ✅ التحقق من صلاحيات SaaS Admin
        $isSaasAdmin = $u->hasAnyRole(['saas-admin', 'system-admin', 'super-admin']) ||
                       (($u->email ?? null) === 'admin@athkahr.com');

        // ✅ إذا كان SaaS Admin على الدومين المركزي -> السماح بالدخول
        if ($isSaasAdmin && $isOnCentralDomain) {
            return $next($request);
        }

        // ✅ إذا كان Company Admin فقط (وليس SaaS Admin) -> إعادة توجيه
        $isCompanyAdminOnly = ($u->hasRole('company-admin') || ! empty($u->saas_company_id)) && ! $isSaasAdmin;
        if ($isCompanyAdminOnly) {
            return redirect()->route('company-admin.hello');
        }

        // ✅ إذا كان SaaS Admin لكن على دومين الشركة -> إعادة توجيه للدومين المركزي
        if ($isSaasAdmin && ! $isOnCentralDomain) {
            $scheme = $request->isSecure() ? 'https' : 'http';
            $port = $request->getPort();
            $portPart = in_array($port, [80, 443], true) ? '' : ':'.$port;
            $target = $scheme.'://'.$central.$portPart.$request->getRequestUri();
            return redirect()->away($target);
        }

        // ✅ إذا لم يكن SaaS Admin -> رفض الوصول
        abort(403);
    }
}
