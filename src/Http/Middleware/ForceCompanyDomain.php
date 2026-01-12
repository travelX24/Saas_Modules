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

        $company = SaasCompany::find($user->saas_company_id);
        if (! $company || empty($company->slug)) {
            return $next($request);
        }

        $base = strtolower(env('TENANT_BASE_DOMAIN', 'athkahr.lvh.me'));
        $desiredHost = strtolower($company->primary_domain.'.'.$base);

        $currentHost = strtolower($request->getHost());

        // لو نحن فعلًا على الدومين الصحيح: كمل
        if ($currentHost === $desiredHost) {
            return $next($request);
        }

        // ابني نفس الرابط لكن على الدومين الجديد
        $scheme = $request->isSecure() ? 'https' : 'http';
        $port = $request->getPort();

        $portPart = '';
        if (! in_array($port, [80, 443], true)) {
            $portPart = ':'.$port; // مهم في local (8000)
        }

        $target = $scheme.'://'.$desiredHost.$portPart.$request->getRequestUri();

        return redirect()->away($target);
    }
}
