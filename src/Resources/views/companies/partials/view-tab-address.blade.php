<div class="space-y-3">
    {{-- Contact --}}
    <div>
        <div class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">{{ tr('Contact') }}</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Official Email') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->official_email ?: '-' }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Phone 1') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->phone_1 ?: '-' }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Phone 2') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->phone_2 ?: '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="h-px bg-gray-100"></div>

    {{-- Address --}}
    <div>
        <div class="text-xs sm:text-sm font-bold text-gray-900 mb-1.5">{{ tr('Address') }}</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Country') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->country ?: '-' }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('City') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->city ?: '-' }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Region') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->region ?: '-' }}
                </div>
            </div>

            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Address') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->address_line ?: '-' }}
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    {{ tr('Postal Code') }}
                </label>
                <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                    {{ $company->postal_code ?: '-' }}
                </div>
            </div>
        </div>
    </div>

    <div class="h-px bg-gray-100"></div>

    {{-- Coordinates --}}
    @if($company->lat && $company->lng)
        <div class="space-y-2" x-data="viewLocationModal({{ $company->lat }}, {{ $company->lng }})">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div class="text-xs sm:text-sm font-bold text-gray-900">{{ tr('Location Coordinates') }}</div>
                <button
                    type="button"
                    @click="openModal()"
                    class="w-full sm:w-auto px-3 py-1.5 rounded-xl border border-[color:var(--brand-via)] bg-white text-[color:var(--brand-via)] font-semibold
                           hover:bg-[color:var(--brand-via)] hover:text-white transition-all duration-200
                           flex items-center justify-center gap-2 shadow-sm text-xs sm:text-sm"
                >
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ tr('View Location') }}</span>
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        {{ tr('Lat') }}
                    </label>
                    <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                        {{ $company->lat }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        {{ tr('Lng') }}
                    </label>
                    <div class="text-sm text-gray-900 bg-gray-50 rounded-xl px-3 py-2 border border-gray-200 min-h-[38px] flex items-center">
                        {{ $company->lng }}
                    </div>
                </div>
            </div>

            {{-- Map Modal --}}
            <div
                x-show="mapModalOpen"
                x-cloak
                @keydown.escape.window="closeModal()"
                class="fixed inset-0 z-[1100] flex items-center justify-center p-0 sm:p-2 md:p-4 bg-black/50 backdrop-blur-sm"
                style="display: none;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div
                    @click.away="closeModal()"
                    class="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-[95%] h-[85vh] sm:w-[85%] sm:h-[75vh] md:w-[70%] md:h-[70vh] lg:w-[60%] lg:h-[65vh] max-w-4xl max-h-[85vh] flex flex-col overflow-hidden mx-auto"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                >
                    {{-- Header --}}
                    <div class="px-4 sm:px-6 py-4 sm:py-5 bg-gradient-to-br from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] flex items-center justify-between">
                        <h3 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ tr('Location on Map') }}</span>
                        </h3>
                        <button
                            type="button"
                            @click="closeModal()"
                            class="w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center rounded-xl text-white hover:bg-white/20 active:scale-95 transition-all duration-200"
                        >
                            <i class="fas fa-times text-lg sm:text-xl"></i>
                        </button>
                    </div>

                    {{-- Map Container --}}
                    <div class="flex-1 relative min-h-[calc(100vh-200px)] sm:min-h-[400px]">
                        <div x-ref="mapContainer" class="w-full h-full" style="min-height: calc(100vh - 200px);"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
