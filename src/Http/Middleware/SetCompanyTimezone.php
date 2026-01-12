<?php

namespace Athka\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCompanyTimezone
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ تطبيق timezone من الشركة (إذا كان company admin)
        if (app()->bound('currentCompany')) {
            $company = app('currentCompany');
            if ($company && $company->settings && $company->settings->timezone) {
                $timezone = $company->settings->timezone;
                
                // التحقق من أن timezone صحيح
                if (in_array($timezone, timezone_identifiers_list(), true)) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }
            }
        }

        return $next($request);
    }
}

