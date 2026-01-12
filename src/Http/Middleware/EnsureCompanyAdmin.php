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

        // ✅ Company Admin
        if ($u->hasRole('company-admin') || ! empty($u->saas_company_id)) {
            return $next($request);
        }

        // ✅ SaaS Admin يرجعه للوحة SaaS
        if ($u->hasAnyRole(['saas-admin', 'system-admin', 'super-admin'])) {
            return redirect()->route('saas.dashboard');
        }

        abort(403);
    }
}
