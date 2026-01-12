<?php

namespace Athka\Saas\Livewire\Dashboard;

use App\Models\User;
use Athka\Saas\Models\SaasCompany;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Index extends Component
{
    public string $activeTab = 'companies';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    
        // ✅ اطلب من الواجهة إعادة رسم الشارت بعد تحديث Livewire
        $this->dispatch('charts:refresh');
    }
    

    public function render()
    {
        // إحصائيات الشركات (Cache لمدة 5 دقائق)
        $cacheKey = 'dashboard:stats:'.now()->format('Y-m-d-H');
        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return [
                'totalCompanies' => SaasCompany::count(),
                'activeCompanies' => $this->getActiveCompaniesCount(),
                'expiredCompanies' => $this->getExpiredCompaniesCount(),
                'totalUsers' => User::whereNotNull('saas_company_id')->count(),
                'newUsersThisMonth' => User::whereNotNull('saas_company_id')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'subscriptionsExpiringSoon' => $this->getSubscriptionsExpiringSoonCount(),
                'newCompaniesThisMonth' => SaasCompany::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];
        });

        // بيانات الرسم البياني (Cache لمدة 10 دقائق)
        $chartCacheKey = 'dashboard:charts:'.now()->format('Y-m-d-H');
$charts = Cache::remember($chartCacheKey, now()->addMinutes(5), function () {

            return [
                'companiesChartData' => $this->getCompaniesChartData(),
                'usersChartData' => $this->getUsersChartData(),
                'subscriptionsChartData' => $this->getSubscriptionsChartData(),
            ];
        });

        // الشركات الحديثة (Cache لمدة 2 دقيقة)
        $recentCacheKey = 'dashboard:recent:'.now()->format('Y-m-d-H-i');
        $recent = Cache::remember($recentCacheKey, now()->addMinutes(2), function () {
            return [
                'recentCompanies' => SaasCompany::with(['settings', 'users'])
                    ->latest()
                    ->limit(5)
                    ->get(),
                'recentUsers' => User::with('roles')
                    ->whereNotNull('saas_company_id')
                    ->latest()
                    ->limit(5)
                    ->get(),
                'companiesByIndustry' => SaasCompany::whereNotNull('main_industry')
                    ->selectRaw('main_industry, COUNT(*) as count')
                    ->groupBy('main_industry')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get(),
            ];
        });

        return view('saas::dashboard.index', [
            'totalCompanies' => $stats['totalCompanies'],
            'activeCompanies' => $stats['activeCompanies'],
            'expiredCompanies' => $stats['expiredCompanies'],
            'totalUsers' => $stats['totalUsers'],
            'newUsersThisMonth' => $stats['newUsersThisMonth'],
            'subscriptionsExpiringSoon' => $stats['subscriptionsExpiringSoon'],
            'newCompaniesThisMonth' => $stats['newCompaniesThisMonth'],
            'companiesChartData' => $charts['companiesChartData'],
            'usersChartData' => $charts['usersChartData'],
            'subscriptionsChartData' => $charts['subscriptionsChartData'],
            'recentCompanies' => $recent['recentCompanies'],
            'recentUsers' => $recent['recentUsers'],
            'companiesByIndustry' => $recent['companiesByIndustry'],
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }

    private function getActiveCompaniesCount(): int
    {
        // استخدام join بدلاً من whereHas لتحسين الأداء
        return SaasCompany::join('saas_company_otherinfo', 'saas_companies.id', '=', 'saas_company_otherinfo.company_id')
            ->where('saas_company_otherinfo.subscription_ends_at', '>=', now())
            ->distinct('saas_companies.id')
            ->count('saas_companies.id');
    }

    private function getExpiredCompaniesCount(): int
    {
        // استخدام join بدلاً من whereHas لتحسين الأداء
        return SaasCompany::leftJoin('saas_company_otherinfo', 'saas_companies.id', '=', 'saas_company_otherinfo.company_id')
            ->where(function ($query) {
                $query->whereNull('saas_company_otherinfo.subscription_ends_at')
                    ->orWhere('saas_company_otherinfo.subscription_ends_at', '<', now());
            })
            ->distinct('saas_companies.id')
            ->count('saas_companies.id');
    }

    private function getSubscriptionsExpiringSoonCount(): int
    {
        // استخدام join بدلاً من whereHas لتحسين الأداء
        return SaasCompany::join('saas_company_otherinfo', 'saas_companies.id', '=', 'saas_company_otherinfo.company_id')
            ->whereBetween('saas_company_otherinfo.subscription_ends_at', [now(), now()->addDays(30)])
            ->distinct('saas_companies.id')
            ->count('saas_companies.id');
    }

    private function getCompaniesChartData(): array
    {
        $cacheKey = 'dashboard:chart:companies:'.now()->format('Y-m-d-H'); // يتجدد كل ساعة

        return Cache::remember($cacheKey, now()->addMinutes(5), function () { // 5 دقائق كافية
        
            $months = [];
            $counts = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $months[] = $date->format('M Y');
                $counts[] = SaasCompany::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }

            return [
                'labels' => $months,
                'data' => $counts,
            ];
        });
    }

    private function getUsersChartData(): array
    {
        $cacheKey = 'dashboard:chart:users:'.now()->format('Y-m-d-H');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
        
            $months = [];
            $counts = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $months[] = $date->format('M Y');
                $counts[] = User::whereNotNull('saas_company_id')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }

            return [
                'labels' => $months,
                'data' => $counts,
            ];
        });
    }

    private function getSubscriptionsChartData(): array
    {
        $cacheKey = 'dashboard:chart:subscriptions:'.now()->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHours(1), function () {
            $active = $this->getActiveCompaniesCount();
            $expired = $this->getExpiredCompaniesCount();
            $expiringSoon = $this->getSubscriptionsExpiringSoonCount();

            return [
                'labels' => [tr('Active'), tr('Expired'), tr('Expiring Soon')],
                'data' => [$active, $expired, $expiringSoon],
            ];
        });
    }
}
