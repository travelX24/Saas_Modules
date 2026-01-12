@php
    $settings = $company->settings;
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
            $allTimezones[$tz] = str_replace('_', ' ', $tz) . ' (UTC' . $offsetStr . ')';
        } catch (\Exception $e) {
            // Skip invalid timezones
        }
    }
    
    $datetimeFormats = [
        'Y-m-d H:i:s' => tr('ISO Format') . ' (Y-m-d H:i:s) - 2025-12-31 15:30:45',
        'Y-m-d H:i' => tr('ISO Short') . ' (Y-m-d H:i) - 2025-12-31 15:30',
        'd/m/Y H:i' => tr('European') . ' (d/m/Y H:i) - 31/12/2025 15:30',
        'm/d/Y h:i A' => tr('US Format') . ' (m/d/Y h:i A) - 12/31/2025 03:30 PM',
        'd-m-Y H:i' => tr('European Dash') . ' (d-m-Y H:i) - 31-12-2025 15:30',
        'Y/m/d H:i' => tr('Slash Format') . ' (Y/m/d H:i) - 2025/12/31 15:30',
        'd M Y H:i' => tr('Text Month') . ' (d M Y H:i) - 31 Dec 2025 15:30',
        'd F Y H:i' => tr('Full Month') . ' (d F Y H:i) - 31 December 2025 15:30',
        'l, d F Y H:i' => tr('Full Date') . ' (l, d F Y H:i) - Monday, 31 December 2025 15:30',
    ];
@endphp

<div class="space-y-3">
    {{-- Numbers --}}
    @if($settings && ($settings->license_number || $settings->tax_number || $settings->cr_number))
        <div>
            <div class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">{{ tr('Company Numbers') }}</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                @if($settings->license_number)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('License Number') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $settings->license_number }}
                        </div>
                    </div>
                @endif

                @if($settings->tax_number)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Tax Number') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $settings->tax_number }}
                        </div>
                    </div>
                @endif

                @if($settings->cr_number)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('CR Number') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $settings->cr_number }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="h-px bg-gray-100"></div>
    @endif

    {{-- Subscription --}}
    @if($settings)
        <div>
            <div class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">{{ tr('Subscription') }}</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                @if($settings->subscription_starts_at)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Subscription Start') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $settings->subscription_starts_at->format('Y-m-d') }}
                        </div>
                    </div>
                @endif

                @if($settings->subscription_ends_at)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Subscription End') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $settings->subscription_ends_at->format('Y-m-d') }}
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        {{ tr('Allowed Users') }}
                    </label>
                    <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                        {{ $settings->allowed_users ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="h-px bg-gray-100"></div>

        {{-- Settings --}}
        <div>
            <div class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">{{ tr('System Settings') }}</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                @if($settings->timezone)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Timezone') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $allTimezones[$settings->timezone] ?? $settings->timezone }}
                        </div>
                    </div>
                @endif

                @if($settings->default_locale)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('Default Locale') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $settings->default_locale === 'ar' ? tr('Arabic') . ' (ar)' : tr('English') . ' (en)' }}
                        </div>
                    </div>
                @endif

                @if($settings->datetime_format)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            {{ tr('DateTime Format') }}
                        </label>
                        <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                            {{ $datetimeFormats[$settings->datetime_format] ?? $settings->datetime_format }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
