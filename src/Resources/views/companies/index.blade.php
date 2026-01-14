<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
            {{ tr('Companies') }}
        </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ tr('Manage and monitor all companies') }}
            </p>
        </div>

        <x-ui.primary-button
            href="{{ route('saas.companies.create') }}"
            :arrow="false"
            :fullWidth="false"
        >
            <i class="fas fa-plus"></i>
            <span class="ms-2">{{ tr('Add Company') }}</span>
        </x-ui.primary-button>
    </div>

    {{-- Search and Filters --}}
    <x-ui.card>
        <div class="space-y-4">
            {{-- Search Bar --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <x-ui.search-box
                    model="search"
                    :placeholder="tr('Search by name, domain, or slug...')"
                    :debounce="300"
                />
            </div>

            {{-- Filters Header with View Toggle --}}
            <div 
                x-data="{ open: @js(true) }"
                class="space-y-3"
            >
                <div class="flex items-center justify-between">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center justify-between text-sm font-semibold text-gray-700 hover:text-gray-900 transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>{{ tr('Filters') }}</span>
                        </span>
                        <i 
                            class="fas fa-chevron-down transition-transform ms-2"
                            :class="open ? 'rotate-180' : ''"
                        ></i>
                    </button>
                    
                    {{-- View Toggle Buttons --}}
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="setViewMode('list')"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                                {{ $viewMode === 'list' 
                                    ? 'bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg hover:shadow-xl hover:from-amber-600 hover:to-yellow-600' 
                                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 shadow-sm' }}"
                            title="{{ tr('List View') }}"
                        >
                            <i class="fas fa-list"></i>
                        </button>
                        <button
                            type="button"
                            wire:click="setViewMode('cards')"
                            class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200
                                {{ $viewMode === 'cards' 
                                    ? 'bg-gradient-to-r from-amber-500 to-yellow-500 text-white shadow-lg hover:shadow-xl hover:from-amber-600 hover:to-yellow-600' 
                                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 shadow-sm' }}"
                            title="{{ tr('Card View') }}"
                        >
                            <i class="fas fa-th"></i>
                        </button>
                    </div>
                </div>

                {{-- Filters Content --}}
                <div 
                    x-show="open"
                    x-transition
                    class="flex flex-col sm:flex-row gap-3 flex-wrap items-end"
                >
                    <x-ui.filter-select
                        model="statusFilter"
                        :label="tr('Status')"
                        :placeholder="tr('All Status')"
                        :options="[
                            ['value' => 'active', 'label' => tr('Active')],
                            ['value' => 'expired', 'label' => tr('Expired')],
                            ['value' => 'inactive', 'label' => tr('Inactive')],
                            ['value' => 'expiring_soon', 'label' => tr('Expiring Soon (30 days)')],
                        ]"
                        width="md"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />

                    <x-ui.filter-select
                        model="industryFilter"
                        :label="tr('Industry')"
                        :placeholder="tr('All Industries')"
                        :options="$industries"
                        width="lg"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />

                    <x-ui.filter-select
                        model="cityFilter"
                        :label="tr('City')"
                        :placeholder="tr('All Cities')"
                        :options="$cities"
                        width="md"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />

                    <x-ui.filter-select
                        model="countryFilter"
                        :label="tr('Country')"
                        :placeholder="tr('All Countries')"
                        :options="$countries"
                        width="md"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />

                    <x-ui.filter-select
                        model="companyTypeFilter"
                        :label="tr('Company Type')"
                        :placeholder="tr('All Types')"
                        :options="$companyTypes"
                        width="md"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />
                </div>

                {{-- Clear Filters Button --}}
                <div 
                    x-data="{ 
                        hasFilters() {
                            const filterSelects = document.querySelectorAll('select.hidden[wire\\:model], select.hidden[wire\\:model\\.defer], select.hidden[wire\\:model\\.live]');
                            return Array.from(filterSelects).some(el => {
                                const value = el.value;
                                return value && value !== '' && value !== 'all';
                            });
                        },
                        clearAll() {
                            if ($wire && typeof $wire.clearAllFilters === 'function') {
                                $wire.clearAllFilters();
                            }
                        }
                    }"
                    x-show="hasFilters()"
                    x-transition
                    class="flex items-center justify-end"
                >
                    <button
                        type="button"
                        @click="clearAll()"
                        wire:loading.attr="disabled"
                        wire:target="clearAllFilters"
                        class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 transition-colors disabled:opacity-50"
                    >
                        <i class="fas fa-times" wire:loading.remove wire:target="clearAllFilters"></i>
                        <i class="fas fa-spinner fa-spin" wire:loading wire:target="clearAllFilters"></i>
                        <span wire:loading.remove wire:target="clearAllFilters">{{ tr('Clear all filters') }}</span>
                        <span wire:loading wire:target="clearAllFilters">{{ tr('Clearing...') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Companies Display --}}
    @if($companies->count() > 0)
        @if($viewMode === 'list')
            {{-- List View --}}
            @php
                $locale = app()->getLocale();
                $isRtl = in_array(substr($locale, 0, 2), ['ar', 'fa', 'ur', 'he']);
                $textAlign = $isRtl ? 'text-right' : 'text-left';
                $dir = $isRtl ? 'rtl' : 'ltr';
            @endphp
            <x-ui.card>
                <x-ui.table 
                    :headers="[
                        tr('Company'),
                        tr('Industry'),
                        tr('Status'),
                        tr('Subscription'),
                        tr('Users'),
                        tr('Location'),
                        tr('Created'),
                        tr('Actions'),
                    ]"
                    :rtl="$isRtl"
                    :perPage="10"
                >
                    @foreach($companies as $company)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            {{-- Company Name & Logo --}}
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2">
                                    @if($company->logo_path)
                                        @php
                                            $cleanPath = str_replace('\\', '/', $company->logo_path);
                                            $cleanPath = ltrim($cleanPath, '/');
                                            $logoUrl = asset('storage/' . $cleanPath) . '?v=' . $company->updated_at->timestamp;
                                        @endphp
                                        <img 
                                            src="{{ $logoUrl }}" 
                                            alt="{{ app()->getLocale() === 'ar' ? $company->legal_name_ar : ($company->legal_name_en ?? $company->legal_name_ar) }}"
                                            class="w-9 h-9 rounded-lg object-cover border border-gray-200 flex-shrink-0"
                                            loading="lazy"
                                            onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        />
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-center flex-shrink-0" style="display: none;">
                                            <i class="fas fa-building text-white text-xs"></i>
                                        </div>
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-building text-white text-xs"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-gray-900 text-sm truncate" title="@if(app()->getLocale() === 'ar'){{ $company->legal_name_ar }}@else{{ $company->legal_name_en ?: $company->legal_name_ar }}@endif">
                                            @if(app()->getLocale() === 'ar')
                                                {{ $company->legal_name_ar }}
                                            @else
                                                {{ $company->legal_name_en ?: $company->legal_name_ar }}
                                            @endif
                                        </div>
                                        @if($company->primary_domain)
                                            <div class="text-xs text-gray-500 truncate mt-0.5" title="{{ $company->primary_domain }}">
                                                {{ $company->primary_domain }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Industry --}}
                            <td class="py-3 px-3">
                                @if($company->main_industry)
                                    @php
                                        $configIndustries = config('industries.main_industries', []);
                                        $industryLabel = $company->main_industry;
                                        if (isset($configIndustries[$company->main_industry])) {
                                            $industryLabel = $locale === 'en' ? $configIndustries[$company->main_industry] : $company->main_industry;
                                        }
                                    @endphp
                                    <div class="text-sm text-gray-700 truncate" title="{{ $industryLabel }}">
                                        <i class="fas fa-industry me-1 text-gray-400"></i>
                                        <span>{{ \Illuminate\Support\Str::limit($industryLabel, 25) }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="py-3 px-3">
                                @if($company->settings)
                                    @php
                                        $isActive = $company->settings->subscription_ends_at && $company->settings->subscription_ends_at->isFuture();
                                        $statusType = $isActive ? 'success' : 'danger';
                                        $statusText = $isActive ? tr('Active') : tr('Expired');
                                    @endphp
                                    <x-ui.badge :type="$statusType" size="sm">
                                        {{ $statusText }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge type="danger" size="sm">
                                        {{ tr('Expired') }}
                                    </x-ui.badge>
                                @endif
                            </td>

                            {{-- Subscription --}}
                            <td class="py-3 px-3">
                                @if($company->settings && $company->settings->subscription_ends_at)
                                    <span class="text-sm text-gray-700">
                                        {{ $company->settings->subscription_ends_at->format('Y-m-d') }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Users --}}
                            <td class="py-3 px-3">
                                @if($company->settings)
                                    <span class="text-sm text-gray-700">
                                        {{ $company->users->count() }} / {{ $company->settings->allowed_users }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Location --}}
                            <td class="py-3 px-3">
                                @if($company->city)
                                    <div class="text-sm text-gray-700 truncate" title="{{ $company->city }}, {{ $company->country }}">
                                        <i class="fas fa-map-marker-alt me-1 text-gray-400"></i>
                                        <span>{{ \Illuminate\Support\Str::limit($company->city . ', ' . $company->country, 20) }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Created Date --}}
                            <td class="py-3 px-3">
                                <span class="text-sm text-gray-700">
                                    {{ $company->created_at->format('Y-m-d') }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="py-3 px-3">
                                <x-ui.dropdown-menu>
                                    <x-ui.dropdown-item 
                                        href="#"
                                        x-on:click="$dispatch('open-view-company-{{ $company->id }}')"
                                    >
                                        <i class="fas fa-eye w-4 me-2"></i>
                                        {{ tr('View & Edit') }}
                                    </x-ui.dropdown-item>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <x-ui.dropdown-item 
                                        :class="$company->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50'"
                                        href="#"
                                        wire:click="toggleCompanyStatus({{ $company->id }})"
                                    >
                                        @if($company->is_active)
                                            <i class="fas fa-pause w-4 me-2"></i>
                                            {{ tr('Deactivate') }}
                                        @else
                                            <i class="fas fa-play w-4 me-2"></i>
                                            {{ tr('Activate') }}
                                        @endif
                                    </x-ui.dropdown-item>
                                </x-ui.dropdown-menu>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        @else
            {{-- Cards View --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($companies as $company)
                <x-ui.card hover="true">
                    <div class="flex items-start justify-between mb-4">
                        {{-- Logo and Name --}}
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            @if($company->logo_path)
                                @php
                                    // تنظيف المسار وإزالة backslashes
                                    $cleanPath = str_replace('\\', '/', $company->logo_path);
                                    $cleanPath = ltrim($cleanPath, '/');
                                    $logoUrl = asset('storage/' . $cleanPath) . '?v=' . $company->updated_at->timestamp;
                                @endphp
                                
                                <img 
                                    src="{{ $logoUrl }}" 
                                    alt="{{ app()->getLocale() === 'ar' ? $company->legal_name_ar : ($company->legal_name_en ?? $company->legal_name_ar) }}"
                                    class="w-12 h-12 rounded-xl object-cover border border-gray-200 flex-shrink-0"
                                    loading="lazy"
                                    onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                />
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-center flex-shrink-0" style="display: none;">
                                    <i class="fas fa-building text-white text-lg"></i>
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-building text-white text-lg"></i>
                                </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm sm:text-base truncate">
                                    @if(app()->getLocale() === 'ar')
                                        {{ $company->legal_name_ar }}
                                    @else
                                        {{ $company->legal_name_en ?: $company->legal_name_ar }}
                                    @endif
                                </h3>
                                @if($company->main_industry)
                                    @php
                                        $locale = app()->getLocale();
                                        $configIndustries = config('industries.main_industries', []);
                                        $industryLabel = $company->main_industry;
                                        
                                        // إذا كانت الصناعة موجودة في config، استخدم الترجمة
                                        if (isset($configIndustries[$company->main_industry])) {
                                            $industryLabel = $locale === 'en' ? $configIndustries[$company->main_industry] : $company->main_industry;
                                        } else {
                                            // محاولة استخدام tr() للترجمة
                                            $industryLabel = $company->main_industry;
                                        }
                                    @endphp
                                    <p class="text-xs text-gray-500 truncate mt-1">
                                        <i class="fas fa-industry me-1"></i>
                                        {{ $industryLabel }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Actions Menu --}}
                        <x-ui.dropdown-menu>
                            <x-ui.dropdown-item 
                                href="#"
                                x-on:click="$dispatch('open-view-company-{{ $company->id }}')"
                            >
                                <i class="fas fa-eye w-4 me-2"></i>
                                {{ tr('View & Edit') }}
                            </x-ui.dropdown-item>
                            <div class="border-t border-gray-100 my-1"></div>
                            <x-ui.dropdown-item 
                                :class="$company->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50'"
                                href="#"
                                wire:click="toggleCompanyStatus({{ $company->id }})"
                            >
                                @if($company->is_active)
                                    <i class="fas fa-pause w-4 me-2"></i>
                                    {{ tr('Deactivate') }}
                                @else
                                    <i class="fas fa-play w-4 me-2"></i>
                                    {{ tr('Activate') }}
                                @endif
                            </x-ui.dropdown-item>
                        </x-ui.dropdown-menu>
                    </div>

                    {{-- Company Info --}}
                    <div class="space-y-2 mb-4">
                        @if($company->settings)
                            @php
                                $isActive = $company->settings->subscription_ends_at && $company->settings->subscription_ends_at->isFuture();
                                $statusType = $isActive ? 'success' : 'danger';
                                $statusText = $isActive ? tr('Active') : tr('Expired');
                            @endphp
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ tr('Status') }}</span>
                                <x-ui.badge :type="$statusType" size="sm">
                                    {{ $statusText }}
                                </x-ui.badge>
                            </div>

                            @if($company->settings->subscription_ends_at)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500">{{ tr('Subscription') }}</span>
                                    <span class="text-xs font-medium text-gray-700">
                                        {{ $company->settings->subscription_ends_at->format('Y-m-d') }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">{{ tr('Users') }}</span>
                                <span class="text-xs font-medium text-gray-700">
                                    {{ $company->users->count() }} / {{ $company->settings->allowed_users }}
                                </span>
                            </div>
                        @endif

                        @if($company->city)
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="truncate">{{ $company->city }}, {{ $company->country }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <i class="fas fa-calendar"></i>
                            <span>{{ $company->created_at->format('Y-m-d') }}</span>
                        </div>
                    </div>
                </x-ui.card>
                @endforeach
            </div>
        @endif

        {{-- Modals and Dialogs --}}
        @foreach($companies as $company)
            {{-- View Company Modal --}}
            @include('saas::components.view-company-modal', ['company' => $company])
        @endforeach
    @else
        {{-- Empty State --}}
        <x-ui.card>
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-building text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ tr('No companies found') }}
                </h3>
                <p class="text-sm text-gray-500 mb-6">
                    @if($search || $statusFilter !== 'all')
                        {{ tr('Try adjusting your search or filters') }}
                    @else
                        {{ tr('Get started by creating your first company') }}
                    @endif
                </p>
    </div>
        </x-ui.card>
    @endif
</div>
