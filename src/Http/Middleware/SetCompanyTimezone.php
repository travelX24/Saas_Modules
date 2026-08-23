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
        $user = $request->user();

        // ✅ 1. Validate active user status for authenticated API requests
        if ($user && $request->expectsJson() && $user->getAttribute('is_active') === false) {
            $msg = function_exists('tr')
                ? tr('Your account is currently inactive.')
                : 'Your account is currently inactive.';

            return response()->json([
                'ok'      => false,
                'error'   => 'user_inactive',
                'message' => $msg,
            ], 403);
        }

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

        // ✅ 2. Validate company active status and subscription expiry for API requests
        if ($user && $company && $request->expectsJson()) {
            if (! $company->is_active) {
                $msg = function_exists('tr')
                    ? tr('Your company account is currently deactivated. Please contact system administration to activate your company account.')
                    : 'Your company account is currently deactivated. Please contact system administration to activate your company account.';

                return response()->json([
                    'ok'      => false,
                    'error'   => 'company_deactivated',
                    'message' => $msg,
                ], 403);
            }

            if ($company->settings && $company->settings->subscription_ends_at && $company->settings->subscription_ends_at->isPast()) {
                $msg = function_exists('tr')
                    ? tr('Your subscription has expired. Please contact system administration to renew your subscription.')
                    : 'Your subscription has expired. Please contact system administration to renew your subscription.';

                return response()->json([
                    'ok'      => false,
                    'error'   => 'subscription_expired',
                    'message' => $msg,
                ], 403);
            }
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