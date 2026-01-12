@php
    $locale = app()->getLocale();
    $subIndustriesPlaceholder = $locale === 'ar' ? 'مثال: طب, تجميل, تقنية' : 'Example: Medicine, Cosmetics, Technology';
@endphp

<div class="space-y-4 sm:space-y-5">
    {{-- السطر 1: Legal Name AR, Legal Name EN, Company Type --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <x-ui.input :label="tr('Legal Name (AR)')" wire:model.defer="legal_name_ar" error="legal_name_ar" :required="true"/>

        <x-ui.input :label="tr('Legal Name (EN)')" wire:model.defer="legal_name_en" error="legal_name_en"/>

        {{-- ✅ IMPORTANT: use model= instead of wire:model --}}
        <x-ui.select :label="tr('Company Type')" model="company_type" error="company_type" :required="true">
            <option value="individual">{{ tr('Individual') }}</option>
            <option value="foundation">{{ tr('Foundation') }}</option>
            <option value="company">{{ tr('Company') }}</option>
        </x-ui.select>
    </div>

    {{-- السطر 2: Logo (يسار) + Main Industry & Sub Industries (يمين، في نفس السطر) + Bio (يمين، تحت Main Industry & Sub Industries) --}}
    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
        {{-- Logo on the left --}}
        <div class="sm:w-56 lg:w-64 shrink-0">
            <x-ui.image
                :label="tr('Logo')"
                name="logo"
                wire:model="logo"
                target="logo"
                :file="$logo"
                :existingImage="isset($isEditMode) && $isEditMode ? $this->logoUrl : null"
                accept="image/*"
                :hint="tr('PNG/JPG — max 2MB')"
                :previewSize="110"
            />
        </div>

        {{-- Main Industry, Sub Industries, and Bio on the right --}}
        <div class="flex-1 space-y-3">
            {{-- Main Industry & Sub Industries in the same row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                {{-- Main Industry --}}
                <div class="space-y-2" x-data="{
                    get showOther() {
                        return $wire.get('main_industry') === 'أخرى';
                    }
                }">
                    {{-- ✅ IMPORTANT: use model= instead of wire:model --}}
                    <x-ui.select
                        :label="tr('Main Industry')"
                        model="main_industry"
                        error="main_industry"
                    >
                        <option value="">{{ tr('Select an industry...') }}</option>

                        @foreach($this->industries as $industry)
                            <option value="{{ $industry['value'] }}">{{ $industry['label'] }}</option>
                        @endforeach
                    </x-ui.select>

                    <div x-show="showOther" x-transition class="mt-2">
                        <x-ui.input
                            :label="tr('Other Industry')"
                            wire:model.defer="main_industry_other"
                            error="main_industry_other"
                            :placeholder="tr('Please specify the industry...')"
                        />
                    </div>
                </div>

                {{-- Sub Industries --}}
                <x-ui.input
                    :label="tr('Sub Industries')"
                    wire:model.defer="sub_industries_text"
                    error="sub_industries_text"
                    :placeholder="$subIndustriesPlaceholder"
                />
            </div>

            {{-- Bio below Main Industry & Sub Industries --}}
            <x-ui.textarea :label="tr('Bio')" wire:model.defer="bio" error="bio" rows="2"/>
        </div>
    </div>

    {{-- السطر 3: Company Admin Name, Company Admin Email, Company Domain --}}
    @if(!isset($isEditMode) || !$isEditMode)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <x-ui.input
                :label="tr('Company Admin Name')"
                wire:model.defer="company_admin_name"
                error="company_admin_name"
                :required="true"
            />

            <x-ui.input
                :label="tr('Company Admin Email')"
                wire:model.defer="company_admin_email"
                error="company_admin_email"
                :required="true"
            />

            <x-ui.input
                :label="tr('Company Domain')"
                wire:model.lazy="primary_domain"
                error="primary_domain"
                :required="true"
            />
        </div>

        <div class="text-xs text-gray-500">
            {{ tr('A password will be generated automatically for the company admin.') }}
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <x-ui.input
                :label="tr('Company Domain')"
                wire:model.lazy="primary_domain"
                error="primary_domain"
                :required="true"
            />
        </div>
    @endif
</div>
