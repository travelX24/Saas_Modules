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

        // ✅ Company Admin لا يدخل /saas أبداً
        if ($u->hasRole('company-admin') || ! empty($u->saas_company_id)) {
            return redirect()->route('company-admin.hello');
        }

        // ✅ SaaS Admin فقط
        if ($u->hasAnyRole(['saas-admin', 'system-admin', 'super-admin'])) {
            return $next($request);
        }

        abort(403);
    }
}
