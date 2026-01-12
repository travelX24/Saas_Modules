<?php

namespace Athka\Saas\Livewire\Companies;

use Athka\Saas\Models\SaasCompany;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all'; // all, active, expired, suspended

    public string $industryFilter = 'all';

    public string $cityFilter = 'all';

    public string $countryFilter = 'all';

    public string $companyTypeFilter = 'all';

    public string $viewMode = 'list'; // 'list' or 'cards'

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'industryFilter' => ['except' => 'all'],
        'cityFilter' => ['except' => 'all'],
        'countryFilter' => ['except' => 'all'],
        'companyTypeFilter' => ['except' => 'all'],
        'viewMode' => ['except' => 'list'],
    ];

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['list', 'cards'])) {
            $this->viewMode = $mode;
        }
    }

    protected $listeners = ['company-updated' => '$refresh'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingIndustryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCountryFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCompanyTypeFilter(): void
    {
        $this->resetPage();
    }

    // دالة لتطبيق الفلاتر عند الضغط على الزر (للحالات التي تستخدم defer)
    public function applyFilters(): void
    {
        // إعادة تعيين الصفحة إلى 1
        $this->resetPage();

        // Livewire سيعيد الرندر تلقائياً بعد تنفيذ هذه الدالة
        // القيم المعلقة (wire:model.defer) سيتم إرسالها تلقائياً عند استدعاء هذه الدالة
    }

    // دالة لمسح جميع الفلاتر
    public function clearAllFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->industryFilter = 'all';
        $this->cityFilter = 'all';
        $this->countryFilter = 'all';
        $this->companyTypeFilter = 'all';

        // إعادة تعيين الصفحة إلى 1
        $this->resetPage();
    }

    public function toggleCompanyStatus(int $companyId): void
    {
        try {
            $company = SaasCompany::findOrFail($companyId);

            // تبديل حالة الشركة (تفعيل/إيقاف)
            $company->is_active = !$company->is_active;
            $company->save();

            // مسح Cache الفلاتر عند التغيير
            $this->clearFiltersCache();

            // إظهار رسالة نجاح
            $status = $company->is_active ? tr('Company activated successfully') : tr('Company deactivated successfully');
            session()->flash('status', $status);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to update company status. Please try again.'));
        }
    }

    private function clearFiltersCache(): void
    {
        $locale = app()->getLocale();
        Cache::forget("companies:filters:industries:{$locale}");
        Cache::forget("companies:filters:locations:{$locale}");
    }

    public function render()
    {
        $query = SaasCompany::with(['settings', 'users', 'documents'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('legal_name_ar', 'like', '%'.$this->search.'%')
                        ->orWhere('legal_name_en', 'like', '%'.$this->search.'%')
                        ->orWhere('primary_domain', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                if ($this->statusFilter === 'active') {
                    // الشركات النشطة: لديها settings و subscription_ends_at في المستقبل
                    $q->whereHas('settings', function ($query) {
                        $query->whereNotNull('subscription_ends_at')
                            ->where('subscription_ends_at', '>=', now());
                    });
                } elseif ($this->statusFilter === 'expired') {
                    // الشركات المنتهية: ليس لها settings أو subscription_ends_at في الماضي أو null
                    $q->where(function ($query) {
                        $query->whereDoesntHave('settings')
                            ->orWhereHas('settings', function ($subQuery) {
                                $subQuery->where(function ($q) {
                                    $q->whereNull('subscription_ends_at')
                                        ->orWhere('subscription_ends_at', '<', now());
                                });
                            });
                    });
                }
            })
            ->when($this->industryFilter !== 'all', function ($q) {
                $q->where('main_industry', $this->industryFilter);
            })
            ->when($this->cityFilter !== 'all', function ($q) {
                $q->where('city', $this->cityFilter);
            })
            ->when($this->countryFilter !== 'all', function ($q) {
                $q->where('country', $this->countryFilter);
            })
            ->when($this->companyTypeFilter !== 'all', function ($q) {
                $q->where('company_type', $this->companyTypeFilter);
            })
            ->latest();

        $companies = $query->paginate(12);

        // جلب القيم الفريدة للفلاتر مع Cache (15 دقيقة)
        $locale = app()->getLocale();

        // Cache للصناعات
        $industriesCacheKey = "companies:filters:industries:{$locale}";
        $allIndustries = Cache::remember($industriesCacheKey, now()->addMinutes(15), function () use ($locale) {
            $configIndustries = config('industries.main_industries', []);
            $dbIndustries = SaasCompany::whereNotNull('main_industry')
                ->distinct()
                ->pluck('main_industry')
                ->toArray();

            return collect($configIndustries)
                ->keys()
                ->merge($dbIndustries)
                ->unique()
                ->map(function ($industry) use ($configIndustries, $locale) {
                    if (isset($configIndustries[$industry])) {
                        $label = $locale === 'en' ? $configIndustries[$industry] : $industry;
                    } else {
                        $label = function_exists('tr') ? tr($industry) : $industry;
                    }

                    return [
                        'value' => $industry,
                        'label' => $label,
                    ];
                })
                ->toArray();
        });

        // Cache للمدن والدول (عرض كل البيانات بغض النظر عن اللغة)
        $filtersData = Cache::remember("companies:filters:locations:{$locale}", now()->addMinutes(15), function () {
            $allCities = SaasCompany::whereNotNull('city')
                ->distinct()
                ->pluck('city')
                ->sort()
                ->map(fn ($city) => ['value' => $city, 'label' => $city])
                ->values()
                ->toArray();

            $allCountries = SaasCompany::whereNotNull('country')
                ->distinct()
                ->pluck('country')
                ->sort()
                ->map(fn ($country) => ['value' => $country, 'label' => $country])
                ->values()
                ->toArray();

            return [
                'cities' => $allCities,
                'countries' => $allCountries,
            ];
        });

        $allCities = $filtersData['cities'];
        $allCountries = $filtersData['countries'];

        // إضافة خيار "All" في بداية كل قائمة
        $industries = array_merge([['value' => 'all', 'label' => tr('All Industries')]], $allIndustries);
        $cities = array_merge([['value' => 'all', 'label' => tr('All Cities')]], $allCities);
        $countries = array_merge([['value' => 'all', 'label' => tr('All Countries')]], $allCountries);

        // أنواع الشركات
        $companyTypes = [
            ['value' => 'all', 'label' => tr('All Types')],
            ['value' => 'individual', 'label' => tr('Individual')],
            ['value' => 'foundation', 'label' => tr('Foundation')],
            ['value' => 'company', 'label' => tr('Company')],
        ];

        return view('saas::companies.index', [
            'companies' => $companies,
            'industries' => $industries,
            'cities' => $cities,
            'countries' => $countries,
            'companyTypes' => $companyTypes,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
