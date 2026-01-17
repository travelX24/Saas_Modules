<div class="space-y-4">
    {{-- Header --}}
    <x-ui.page-header
        :title="tr('Dashboard')"
        :subtitle="tr('Welcome back! Here\'s what\'s happening with your system.')"
        titleSize="2xl"
    >
        <x-slot:action>
            <div class="text-xs sm:text-sm text-gray-500 bg-white px-4 py-2 rounded-xl border border-gray-200">
                <i class="fas fa-calendar-alt me-2"></i>
                @php
                    // استخدام timezone من config أو env variable
                    // يمكن إضافة APP_TIMEZONE في .env file
                    $timezone = env('APP_TIMEZONE', config('app.timezone', 'Asia/Riyadh'));
                    
                    // إذا كان UTC، استخدم timezone محلي افتراضي للمنطقة العربية
                    if ($timezone === 'UTC') {
                        $timezone = 'Asia/Riyadh'; // يمكن تغييره حسب المنطقة
                    }
                    
                    $currentTime = now()->setTimezone($timezone);
                @endphp
                {{ $currentTime->toDayDateTimeString() }}
            </div>
        </x-slot:action>
    </x-ui.page-header>

    {{-- Stats Cards --}}
    <div class="flex flex-row gap-3">
        {{-- Total Companies --}}
        <x-ui.card class="relative overflow-hidden p-4 flex-1 min-w-0">
            <div class="absolute top-0 right-0 w-14 h-14 bg-gradient-to-br from-blue-500/10 to-blue-600/5 rounded-bl-full"></div>
            <div class="relative">
                <div class="flex items-center mb-3 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i class="fas fa-building text-white text-base"></i>
                    </div>
                    <div class="text-right flex-1">
                        <div class="text-xs text-gray-500 mb-1">{{ tr('Total Companies') }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($totalCompanies) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs mt-2">
                    <span class="text-green-600 font-semibold">
                        <i class="fas fa-arrow-up"></i> {{ $newCompaniesThisMonth }}
                    </span>
                    <span class="text-gray-500">{{ tr('this month') }}</span>
                </div>
            </div>
        </x-ui.card>

        {{-- Active Companies --}}
        <x-ui.card class="relative overflow-hidden p-4 flex-1 min-w-0">
            <div class="absolute top-0 right-0 w-14 h-14 bg-gradient-to-br from-green-500/10 to-green-600/5 rounded-bl-full"></div>
            <div class="relative">
                <div class="flex items-center mb-3 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i class="fas fa-check-circle text-white text-base"></i>
                    </div>
                    <div class="text-right flex-1">
                        <div class="text-xs text-gray-500 mb-1">{{ tr('Active Companies') }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($activeCompanies) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs mt-2">
                    <span class="text-gray-500">
                        {{ $totalCompanies > 0 ? number_format(($activeCompanies / $totalCompanies) * 100, 1) : 0 }}%
                    </span>
                    <span class="text-gray-500">{{ tr('of total') }}</span>
                </div>
            </div>
        </x-ui.card>

        {{-- Total Users --}}
        <x-ui.card class="relative overflow-hidden p-4 flex-1 min-w-0">
            <div class="absolute top-0 right-0 w-14 h-14 bg-gradient-to-br from-purple-500/10 to-purple-600/5 rounded-bl-full"></div>
            <div class="relative">
                <div class="flex items-center mb-3 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i class="fas fa-users text-white text-base"></i>
                    </div>
                    <div class="text-right flex-1">
                        <div class="text-xs text-gray-500 mb-1">{{ tr('Total Users') }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($totalUsers) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs mt-2">
                    <span class="text-green-600 font-semibold">
                        <i class="fas fa-arrow-up"></i> {{ $newUsersThisMonth }}
                    </span>
                    <span class="text-gray-500">{{ tr('this month') }}</span>
                </div>
            </div>
        </x-ui.card>

        {{-- Expiring Soon --}}
        <x-ui.card class="relative overflow-hidden p-4 flex-1 min-w-0">
            <div class="absolute top-0 right-0 w-14 h-14 bg-gradient-to-br from-orange-500/10 to-orange-600/5 rounded-bl-full"></div>
            <div class="relative">
                <div class="flex items-center mb-3 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-white text-base"></i>
                    </div>
                    <div class="text-right flex-1">
                        <div class="text-xs text-gray-500 mb-1">{{ tr('Expiring Soon') }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($subscriptionsExpiringSoon) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs mt-2">
                    <span class="text-orange-600 font-semibold">
                        <i class="fas fa-clock"></i>
                    </span>
                    <span class="text-gray-500">{{ tr('next 30 days') }}</span>
                </div>
            </div>
        </x-ui.card>

        {{-- Inactive Companies --}}
        <x-ui.card class="relative overflow-hidden p-4 flex-1 min-w-0">
            <div class="absolute top-0 right-0 w-14 h-14 bg-gradient-to-br from-red-500/10 to-red-600/5 rounded-bl-full"></div>
            <div class="relative">
                <div class="flex items-center mb-3 gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-lg flex-shrink-0">
                        <i class="fas fa-pause-circle text-white text-base"></i>
                    </div>
                    <div class="text-right flex-1">
                        <div class="text-xs text-gray-500 mb-1">{{ tr('Inactive Companies') }}</div>
                        <div class="text-xl font-bold text-gray-900">{{ number_format($inactiveCompanies) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs mt-2">
                    <span class="text-red-600 font-semibold">
                        <i class="fas fa-ban"></i>
                    </span>
                    <span class="text-gray-500">{{ tr('deactivated') }}</span>
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Charts Tabs --}}
        <x-ui.card class="lg:col-span-2">
            {{-- Tabs Navigation --}}
            <div class="border-b border-gray-200 mb-4">
                <nav class="flex flex-wrap gap-2 sm:gap-3 justify-center" aria-label="Tabs">
                    <button
                        wire:click="setActiveTab('companies')"
                        type="button"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-lg text-center
                            {{ $activeTab === 'companies' 
                                ? 'border-[color:var(--brand-via)] text-[color:var(--brand-via)] bg-[color:var(--brand-via)]/5' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <i class="fas fa-building me-2"></i>
                        {{ tr('Companies Growth') }}
                    </button>
                    <button
                        wire:click="setActiveTab('users')"
                        type="button"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-lg text-center
                            {{ $activeTab === 'users' 
                                ? 'border-[color:var(--brand-via)] text-[color:var(--brand-via)] bg-[color:var(--brand-via)]/5' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <i class="fas fa-users me-2"></i>
                        {{ tr('Users Growth') }}
                    </button>
                    <button
                        wire:click="setActiveTab('subscriptions')"
                        type="button"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-lg text-center
                            {{ $activeTab === 'subscriptions' 
                                ? 'border-[color:var(--brand-via)] text-[color:var(--brand-via)] bg-[color:var(--brand-via)]/5' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <i class="fas fa-credit-card me-2"></i>
                        {{ tr('Subscriptions Status') }}
                    </button>
                    <button
                        wire:click="setActiveTab('industries')"
                        type="button"
                        class="px-3 py-2 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-lg text-center
                            {{ $activeTab === 'industries' 
                                ? 'border-[color:var(--brand-via)] text-[color:var(--brand-via)] bg-[color:var(--brand-via)]/5' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                    >
                        <i class="fas fa-industry me-2"></i>
                        {{ tr('Top Industries') }}
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="min-h-[280px]">
                {{-- Companies Growth Tab --}}
                @if($activeTab === 'companies')
                    <div wire:key="companies-tab-{{ $activeTab }}" 
                         x-data
                         x-init="setTimeout(() => { initCompaniesChart(); }, 100)">
                        <div class="mb-3">
                            <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ tr('Companies Growth') }}</h3>
                            <p class="text-xs text-gray-500">{{ tr('New companies registered over the last 6 months') }}</p>
                        </div>
                        <div class="h-48">
                            <canvas id="companiesChart"></canvas>
                        </div>
                    </div>
                @endif

                {{-- Users Growth Tab --}}
                @if($activeTab === 'users')
                    <div wire:key="users-tab-{{ $activeTab }}"
                         x-data
                         x-init="setTimeout(() => { initUsersChart(); }, 100)">
                        <div class="mb-3">
                            <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ tr('Users Growth') }}</h3>
                            <p class="text-xs text-gray-500">{{ tr('New users registered over the last 6 months') }}</p>
                        </div>
                        <div class="h-48">
                            <canvas id="usersChart"></canvas>
                        </div>
                    </div>
                @endif

                {{-- Subscriptions Status Tab --}}
                @if($activeTab === 'subscriptions')
                    <div wire:key="subscriptions-tab-{{ $activeTab }}"
                         x-data
                         x-init="setTimeout(() => { initSubscriptionsChart(); }, 100)">
                        <div class="mb-3">
                            <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ tr('Subscriptions Status') }}</h3>
                            <p class="text-xs text-gray-500">{{ tr('Overview of subscription statuses') }}</p>
                        </div>
                        <div class="h-48">
                            <canvas id="subscriptionsChart"></canvas>
                        </div>
                    </div>
                @endif

                {{-- Top Industries Tab --}}
                @if($activeTab === 'industries')
                    <div wire:key="industries-tab">
                        <div class="mb-3">
                            <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ tr('Top Industries') }}</h3>
                            <p class="text-xs text-gray-500">{{ tr('Companies distribution by industry') }}</p>
                        </div>
                        <div class="space-y-3">
                            @forelse($companiesByIndustry as $industry)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ app()->getLocale() === 'ar' ? $industry->main_industry : (config('industries.main_industries.' . $industry->main_industry, $industry->main_industry)) }}
                                            </span>
                                            <span class="text-sm font-bold text-gray-900">{{ $industry->count }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-gradient-to-r from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] h-2 rounded-full transition-all duration-500"
                                                style="width: {{ $companiesByIndustry->max('count') > 0 ? ($industry->count / $companiesByIndustry->max('count')) * 100 : 0 }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <i class="fas fa-industry text-3xl mb-2"></i>
                                    <p class="text-sm">{{ tr('No industry data available') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Recent Companies --}}
        <x-ui.card>
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ tr('Recent Companies') }}</h3>
                    <p class="text-xs text-gray-500">{{ tr('Latest registered companies') }}</p>
                </div>
                @php
                    $locale = app()->getLocale();
                    $isRtl = in_array(substr($locale, 0, 2), ['ar', 'fa', 'ur', 'he']);
                @endphp
                <a href="{{ route('saas.companies.index') }}" class="text-xs text-[color:var(--brand-via)] hover:underline font-medium inline-flex items-center gap-1">
                    {{ tr('View All') }}
                    <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }}"></i>
                </a>
            </div>
            <div class="space-y-2">
                @forelse($recentCompanies as $company)
                    <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-white text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">
                                {{ app()->getLocale() === 'ar' ? $company->legal_name_ar : ($company->legal_name_en ?: $company->legal_name_ar) }}
                            </h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $company->created_at->diffForHumans() }}
                                </span>
                                @if($company->users->count() > 0)
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-users me-1"></i>
                                        {{ $company->users->count() }} {{ tr('users') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($company->settings && $company->settings->subscription_ends_at)
                            @php
                                $isActive = $company->settings->subscription_ends_at->isFuture();
                            @endphp
                            <x-ui.badge :type="$isActive ? 'success' : 'danger'" size="sm">
                                {{ $isActive ? tr('Active') : tr('Expired') }}
                            </x-ui.badge>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-building text-3xl mb-2"></i>
                        <p class="text-sm">{{ tr('No companies found') }}</p>
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</div>

@push('scripts')
<script>
    let companiesChartInstance = null;
    let usersChartInstance = null;
    let subscriptionsChartInstance = null;

    // Chart data from server
    const chartDataStore = {
        companies: {
            labels: @json($companiesChartData['labels']),
            data: @json($companiesChartData['data'])
        },
        users: {
            labels: @json($usersChartData['labels']),
            data: @json($usersChartData['data'])
        },
        subscriptions: {
            labels: @json($subscriptionsChartData['labels']),
            data: @json($subscriptionsChartData['data'])
        }
    };

    function getChartData(chartType) {
        const data = chartDataStore[chartType];
        if (!data || !data.labels || !data.data) {
            console.warn('Chart data not found for:', chartType);
            return { labels: [], data: [] };
        }
        return data;
    }

    function waitForChartJs(callback, maxAttempts = 50) {
        if (typeof Chart !== 'undefined') {
            callback();
            return;
        }
        
        if (maxAttempts <= 0) {
            console.error('Chart.js not loaded after timeout');
            return;
        }
        
        setTimeout(() => waitForChartJs(callback, maxAttempts - 1), 100);
    }

    function initCompaniesChart() {
        waitForChartJs(() => {
            if (companiesChartInstance) {
                companiesChartInstance.destroy();
                companiesChartInstance = null;
            }
            
            const companiesCtx = document.getElementById('companiesChart');
            
            if (!companiesCtx) {
                return;
            }
            
            const chartData = getChartData('companies');
            
            console.log('Companies chart data:', chartData);
            
            if (!chartData.labels.length || !chartData.data.length) {
                console.warn('No chart data available for companies chart', chartData);
                return;
            }
            
            companiesChartInstance = new Chart(companiesCtx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: '{{ tr("Companies") }}',
                        data: chartData.data,
                        borderColor: 'rgb(117, 67, 235)',
                        backgroundColor: 'rgba(117, 67, 235, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(117, 67, 235)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    }

    function initUsersChart() {
        waitForChartJs(() => {
            if (usersChartInstance) {
                usersChartInstance.destroy();
                usersChartInstance = null;
            }
            
            const usersCtx = document.getElementById('usersChart');
            
            if (!usersCtx) {
                return;
            }
            
            const chartData = getChartData('users');
            
            console.log('Users chart data:', chartData);
            
            if (!chartData.labels.length || !chartData.data.length) {
                console.warn('No chart data available for users chart', chartData);
                return;
            }
            
            usersChartInstance = new Chart(usersCtx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: '{{ tr("Users") }}',
                        data: chartData.data,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 2,
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    }

    function initSubscriptionsChart() {
        waitForChartJs(() => {
            if (subscriptionsChartInstance) {
                subscriptionsChartInstance.destroy();
                subscriptionsChartInstance = null;
            }
            
            const subscriptionsCtx = document.getElementById('subscriptionsChart');
            
            if (!subscriptionsCtx) {
                return;
            }
            
            const chartData = getChartData('subscriptions');
            
            console.log('Subscriptions chart data:', chartData);
            
            if (!chartData.labels.length || !chartData.data.length) {
                console.warn('No chart data available for subscriptions chart', chartData);
                return;
            }
            
            subscriptionsChartInstance = new Chart(subscriptionsCtx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                        ],
                        borderColor: [
                            'rgb(34, 197, 94)',
                            'rgb(239, 68, 68)',
                            'rgb(249, 115, 22)',
                        ],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            }
                        }
                    }
                }
            });
        });
    }

    // Make functions globally available for Alpine.js
    window.initCompaniesChart = initCompaniesChart;
    window.initUsersChart = initUsersChart;
    window.initSubscriptionsChart = initSubscriptionsChart;
    document.addEventListener('livewire:init', () => {
    Livewire.on('charts:refresh', () => {
        setTimeout(() => {
            const companiesCtx = document.getElementById('companiesChart');
            const usersCtx = document.getElementById('usersChart');
            const subscriptionsCtx = document.getElementById('subscriptionsChart');

            if (companiesCtx && companiesCtx.offsetParent !== null) {
                initCompaniesChart();
            } else if (usersCtx && usersCtx.offsetParent !== null) {
                initUsersChart();
            } else if (subscriptionsCtx && subscriptionsCtx.offsetParent !== null) {
                initSubscriptionsChart();
            }
        }, 120);
    });
});

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const companiesCtx = document.getElementById('companiesChart');
            const usersCtx = document.getElementById('usersChart');
            const subscriptionsCtx = document.getElementById('subscriptionsChart');
            
            if (companiesCtx && companiesCtx.offsetParent !== null) {
                initCompaniesChart();
            } else if (usersCtx && usersCtx.offsetParent !== null) {
                initUsersChart();
            } else if (subscriptionsCtx && subscriptionsCtx.offsetParent !== null) {
                initSubscriptionsChart();
            }
        }, 300);
    });

    // Re-initialize charts when Livewire updates
    document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            // Wait a bit for DOM to be fully updated
            setTimeout(() => {
                // Check which chart canvas is visible and initialize it
                const companiesCtx = document.getElementById('companiesChart');
                const usersCtx = document.getElementById('usersChart');
                const subscriptionsCtx = document.getElementById('subscriptionsChart');
                
                if (companiesCtx && companiesCtx.offsetParent !== null) {
                    initCompaniesChart();
                } else if (usersCtx && usersCtx.offsetParent !== null) {
                    initUsersChart();
                } else if (subscriptionsCtx && subscriptionsCtx.offsetParent !== null) {
                    initSubscriptionsChart();
                }
            }, 150);
        });
    });
</script>
@endpush
