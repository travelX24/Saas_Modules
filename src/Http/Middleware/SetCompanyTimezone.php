<?php

namespace Athka\Saas\Http\Middleware;

use Athka\Saas\Models\SaasCompany;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SetCompanyTimezone
{
    public function handle(Request $request, Closure $next)
    {
        /*
         * For authenticated API requests, the authenticated user's company
         * is authoritative.
         *
         * For company web requests, ResolveCompanyByDomain normally binds
         * currentCompany before this middleware executes.
         */
        $user = $request->user();

        $companyId = (int) (
            $user?->saas_company_id
            ?? $user?->company_id
            ?? 0
        );

        $company = null;

        if ($companyId > 0) {
            $company = Cache::remember(
                "company:id:{$companyId}",
                now()->addMinutes(10),
                fn () => SaasCompany::with('settings')->find($companyId)
            );

            if ($company) {
                app()->instance('currentCompany', $company);
            }
        } elseif (app()->bound('currentCompany')) {
            $company = app('currentCompany');
        }

        $timezone = $company?->settings?->timezone;

        if (
            is_string($timezone)
            && in_array($timezone, timezone_identifiers_list(), true)
        ) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }

        return $next($request);
    }
}