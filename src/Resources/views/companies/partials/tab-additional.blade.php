@php
    // Prepare timezones for the searchable select
    $allTimezones = [];
    foreach (timezone_identifiers_list() as $tz) {
        try {
            $dtz = new DateTimeZone($tz);
            $dt = new DateTime('now', $dtz);
            $offset = $dtz->getOffset($dt);
            $hours = intval($offset / 3600);
            $minutes = abs(($offset % 3600) / 60);
            $sign = $hours >= 0 ? '+' : '-';
            $offsetStr = sprintf('%s%02d:%02d', $sign, abs($hours), $minutes);
            $allTimezones[] = [
                'value' => $tz,
                'label' => str_replace('_', ' ', $tz) . ' (UTC' . $offsetStr . ')',
            ];
        } catch (\Exception $e) {
            // Skip invalid timezones
        }
    }
@endphp

<div class="space-y-4 sm:space-y-6">

    {{-- Numbers --}}
    <div>
        <div class="text-xs sm:text-sm font-bold text-gray-900 mb-2">{{ tr('Company Numbers') }}</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <x-ui.input :label="tr('License Number')" wire:model.defer="license_number" error="license_number"/>
            <x-ui.input :label="tr('Tax Number')" wire:model.defer="tax_number" error="tax_number"/>
            <x-ui.input :label="tr('CR Number')" wire:model.defer="cr_number" error="cr_number"/>
        </div>
    </div>

    <div class="h-px bg-gray-100"></div>

    {{-- Subscription --}}
    <div>
        <div class="text-sm font-bold text-gray-900 mb-2">{{ tr('Subscription') }}</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <x-ui.input
                type="date"
                :label="tr('Subscription Start')"
                wire:model.blur="subscription_starts_at"
                error="subscription_starts_at"
                :required="true"
            />

            <x-ui.input
                type="date"
                :label="tr('Subscription End')"
                wire:model.blur="subscription_ends_at"
                error="subscription_ends_at"
                :required="true"
            />

            <x-ui.input
                type="number"
                :label="tr('Allowed Users')"
                wire:model.defer="allowed_users"
                error="allowed_users"
                placeholder="10"
                :required="true"
            />
        </div>
    </div>

    <div class="h-px bg-gray-100"></div>

    {{-- Settings --}}
    <div>
        <div class="text-sm font-bold text-gray-900 mb-2">{{ tr('System Settings') }}</div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            {{-- Timezone --}}
            <x-ui.select
                :label="tr('Timezone')"
                wire:model="timezone"
                error="timezone"
                align="up"
            >
                @foreach($allTimezones as $tz)
                    <option value="{{ $tz['value'] }}">{{ $tz['label'] }}</option>
                @endforeach
            </x-ui.select>

            {{-- Default Locale --}}
            <x-ui.select
                :label="tr('Default Locale')"
                wire:model="default_locale"
                error="default_locale"
                align="up"
            >
                <option value="ar">{{ tr('Arabic') }} (ar)</option>
                <option value="en">{{ tr('English') }} (en)</option>
            </x-ui.select>

            {{-- DateTime Format --}}
            <x-ui.select
                :label="tr('DateTime Format')"
                wire:model="datetime_format"
                error="datetime_format"
                align="up"
            >
                <option value="Y-m-d H:i:s">{{ tr('ISO Format') }} (Y-m-d H:i:s) - 2025-12-31 15:30:45</option>
                <option value="Y-m-d H:i">{{ tr('ISO Short') }} (Y-m-d H:i) - 2025-12-31 15:30</option>
                <option value="d/m/Y H:i">{{ tr('European') }} (d/m/Y H:i) - 31/12/2025 15:30</option>
                <option value="m/d/Y h:i A">{{ tr('US Format') }} (m/d/Y h:i A) - 12/31/2025 03:30 PM</option>
                <option value="d-m-Y H:i">{{ tr('European Dash') }} (d-m-Y H:i) - 31-12-2025 15:30</option>
                <option value="Y/m/d H:i">{{ tr('Slash Format') }} (Y/m/d H:i) - 2025/12/31 15:30</option>
                <option value="d M Y H:i">{{ tr('Text Month') }} (d M Y H:i) - 31 Dec 2025 15:30</option>
                <option value="d F Y H:i">{{ tr('Full Month') }} (d F Y H:i) - 31 December 2025 15:30</option>
                <option value="l, d F Y H:i">{{ tr('Full Date') }} (l, d F Y H:i) - Monday, 31 December 2025 15:30</option>
            </x-ui.select>
        </div>

        <div class="text-[11px] text-gray-500 mt-2">
            {{ tr('These settings will be applied when company admin logs in') }}
        </div>
    </div>

</div>
