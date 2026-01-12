@php
    $locale = app()->getLocale();
    $configIndustries = config('industries.main_industries', []);
    
    $companyTypeLabels = [
        'individual' => tr('Individual'),
        'foundation' => tr('Foundation'),
        'company' => tr('Company'),
    ];
    
    $industryLabel = $company->main_industry;
    if (isset($configIndustries[$company->main_industry])) {
        $industryLabel = $locale === 'en' ? $configIndustries[$company->main_industry] : $company->main_industry;
    }
    
    // Company Admin (أول مستخدم مرتبط بالشركة عادة يكون الـ admin)
    $admin = $company->users->first();
@endphp

<div class="space-y-3">
    {{-- السطر 1: Legal Name AR, Legal Name EN, Company Type --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
        {{-- Legal Name AR --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">
                {{ tr('Legal Name (AR)') }}
            </label>
            <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                {{ $company->legal_name_ar ?: '-' }}
            </div>
        </div>

        {{-- Legal Name EN --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">
                {{ tr('Legal Name (EN)') }}
            </label>
            <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                {{ $company->legal_name_en ?: '-' }}
            </div>
        </div>

        {{-- Company Type --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">
                {{ tr('Company Type') }}
            </label>
            <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                {{ $companyTypeLabels[$company->company_type] ?? $company->company_type }}
            </div>
        </div>
    </div>

    {{-- السطر 2: Logo (يسار) + Main Industry & Sub Industries (يمين، في نفس السطر) + Bio (يمين، تحت Main Industry & Sub Industries) --}}
    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
        {{-- Logo on the left --}}
        @if($company->logo_path)
            <div class="sm:w-52 lg:w-60 shrink-0">
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Logo') }}
                </label>
                <div class="bg-gray-50 rounded-xl p-1.5 border border-gray-200">
                    @php
                        $cleanPath = str_replace('\\', '/', $company->logo_path);
                        $cleanPath = ltrim($cleanPath, '/');
                        $logoUrl = asset('storage/' . $cleanPath);
                    @endphp
                    <img src="{{ $logoUrl }}" alt="Logo" class="w-16 h-16 object-cover rounded-lg">
                </div>
            </div>
        @endif

        {{-- Main Industry, Sub Industries, and Bio on the right --}}
        <div class="flex-1 space-y-2">
            {{-- Main Industry & Sub Industries in the same row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                {{-- Main Industry --}}
                @if($company->main_industry)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Main Industry') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $industryLabel }}
                        </div>
                    </div>
                @endif

                {{-- Sub Industries --}}
                @if($company->sub_industries && count($company->sub_industries) > 0)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Sub Industries') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ implode(', ', $company->sub_industries) }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Bio below Main Industry & Sub Industries --}}
            @if($company->bio)
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        {{ tr('Bio') }}
                    </label>
                    <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[50px]">
                        {{ $company->bio }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- السطر 3: Domain + Company Admin Name & Email --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
        {{-- Domain --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">
                {{ tr('Company Domain') }}
            </label>
            <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                {{ $company->primary_domain ?: '-' }}
            </div>
        </div>

        {{-- Company Admin --}}
        @if($admin)
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Company Admin Name') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $admin->name }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Company Admin Email') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $admin->email }}
                </div>
            </div>
        @endif
    </div>
</div>
