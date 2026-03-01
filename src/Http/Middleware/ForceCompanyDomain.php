<?php

namespace Athka\Saas\Http\Middleware;

use Athka\Saas\Models\SaasCompany;
use Closure;
use Illuminate\Http\Request;

class ForceCompanyDomain
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || empty($user->saas_company_id)) {
            return $next($request);
        }

        // ✅ التحقق من الدومين أولاً
        $host = strtolower($request->getHost());
        $base = strtolower(config('saas.tenant_base_domain', env('TENANT_BASE_DOMAIN', 'athkahr.com')));
        $central = strtolower(config('saas.central_domain', env('CENTRAL_DOMAIN', $base)));

        // ✅ لو نحن على nip.io استخرج IP واصنع base/central ديناميكي
        if (preg_match('/\.(\d{1,3}(?:\.\d{1,3}){3})\.nip\.io$/', $host, $m)) {
            $ip = $m[1];
            $base = "athkahr.$ip.nip.io";
            $central = $base;
        }

        // ✅ إذا كان على الدومين المركزي (SaaS) -> لا تعيد التوجيه
        // هذا يسمح للمستخدم بالعمل على SaaS و Company في نفس الوقت
        $isOnCentralDomain = ($host === $central || $host === 'www.'.$central);
        if ($isOnCentralDomain) {
            // ✅ إذا كان على route SaaS -> لا تعيد التوجيه
            if ($request->is('saas*')) {
                return $next($request);
            }
        }

        $company = \Illuminate\Support\Facades\Cache::remember("company:id:{$user->saas_company_id}", now()->addMinutes(10), function () use ($user) {
            return SaasCompany::with('settings')->find($user->saas_company_id);
        });

        if (! $company || empty($company->primary_domain)) {
            return $next($request);
        }

        $desiredHost = strtolower($company->primary_domain.'.'.$base);

        // لو نحن فعلًا على الدومين الصحيح: كمل
        if ($host === $desiredHost) {
            return $next($request);
        }

        // ✅ إعادة التوجيه لدومين الشركة
        $scheme = $request->isSecure() ? 'https' : 'http';
        $port = $request->getPort();

        $portPart = '';
        if (! in_array($port, [80, 443], true)) {
            $portPart = ':'.$port;
        }

        $target = $scheme.'://'.$desiredHost.$portPart.$request->getRequestUri();

        return redirect()->away($target);
    }
}
