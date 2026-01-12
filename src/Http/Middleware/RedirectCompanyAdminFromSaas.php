<?php

namespace Athka\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCompanyAdminFromSaas
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();

        if ($u) {
            $isCompanyAdmin = false;

            // ✅ الأفضل: Roles (Spatie)
            if (method_exists($u, 'hasRole')) {
                $isCompanyAdmin = $u->hasRole('company-admin');
            } else {
                // ✅ fallback (لو ما عندك Roles)
                // اعتبره Company Admin لو مرتبط بشركة
                if (isset($u->saas_company_id) && ! empty($u->saas_company_id)) {
                    $isCompanyAdmin = true;
                }
            }

            if ($isCompanyAdmin) {
                return redirect()->route('company-admin.hello');
            }
        }

        return $next($request);
    }
}
