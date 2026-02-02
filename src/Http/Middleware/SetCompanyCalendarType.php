<?php

namespace Athka\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SetCompanyCalendarType
{
    public function handle(Request $request, Closure $next)
    {
        $companyId = (int) (
            auth()->user()?->saas_company_id
            ?? auth()->user()?->company_id
            ?? auth()->user()?->company?->id
            ?? session('saas_company_id')
            ?? session('current_saas_company_id')
            ?? session('company_id')
            ?? session('current_company_id')
            ?? 0
        );


        $type = 'gregorian';

        if ($companyId > 0) {
            $type = Cache::remember(
                "company_calendar_type:v2:{$companyId}",
                now()->addMinutes(10),
              fn () => (string) (
                    DB::table('operational_calendars')
                        ->where('company_id', $companyId)
                        ->value('calendar_type')
                    ?? 'gregorian'
                )

            );
        }

        config(['company.calendar_type' => $type]);

        return $next($request);
    }
}
