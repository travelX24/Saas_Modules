<div class="space-y-4 sm:space-y-6">

    {{-- Contact --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <x-ui.input
            :label="tr('Official Email')"
            wire:model.defer="official_email"
            error="official_email"
            placeholder="info@company.com"
            :required="true"
            :requiredText="tr('Required (or Phone 1)')"
        />

        <x-ui.input
            :label="tr('Phone 1')"
            wire:model.defer="phone_1"
            error="phone_1"
            placeholder="+967..."
            :required="true"
            :requiredText="tr('Required (or Official Email)')"
        />

        <x-ui.input
            :label="tr('Phone 2')"
            wire:model.defer="phone_2"
            error="phone_2"
            placeholder="+967..."
        />
    </div>

    <div class="h-px bg-gray-100"></div>

    {{-- Address --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <x-ui.input
            :label="tr('Country')"
            wire:model.defer="country"
            error="country"
            :required="true"
        />

        <x-ui.input
            :label="tr('City')"
            wire:model.defer="city"
            error="city"
            :required="true"
        />

        <x-ui.input
            :label="tr('Region')"
            wire:model.defer="region"
            error="region"
        />

        <div class="sm:col-span-2 lg:col-span-2">
            <x-ui.input
                :label="tr('Address')"
                wire:model.defer="address_line"
                error="address_line"
                placeholder="Street, Building, ..."
            />
        </div>

        <x-ui.input
            :label="tr('Postal Code')"
            wire:model.defer="postal_code"
            error="postal_code"
        />
    </div>

    <div class="h-px bg-gray-100"></div>

    {{-- Coordinates --}}
    <div class="space-y-3 sm:space-y-4" x-data="mapPickerModal">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-3">
            <label class="block text-sm font-semibold text-gray-700">
                {{ tr('Location Coordinates') }}
            </label>
            <button
                type="button"
                @click="openModal()"
                class="w-full sm:w-auto px-4 py-2 rounded-xl border border-[color:var(--brand-via)] bg-white text-[color:var(--brand-via)] font-semibold
                       hover:bg-[color:var(--brand-via)] hover:text-white transition-all duration-200
                       flex items-center justify-center gap-2 shadow-sm text-sm sm:text-base"
            >
                <i class="fas fa-map-marker-alt"></i>
                <span>{{ tr('Choose from Map') }}</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <x-ui.input
                :label="tr('Lat')"
                wire:model.defer="lat"
                error="lat"
                placeholder="15.3694"
                readonly
            />

            <x-ui.input
                :label="tr('Lng')"
                wire:model.defer="lng"
                error="lng"
                placeholder="44.1910"
                readonly
            />
        </div>

        {{-- Map Modal --}}
        <div
            x-show="mapModalOpen"
            x-cloak
            @keydown.escape.window="closeModal()"
            class="fixed inset-0 z-[1100] flex items-center justify-center p-0 sm:p-2 md:p-4 bg-black/50 backdrop-blur-sm"
            style="display: none;"
        >
            <div
                @click.away="closeModal()"
                class="bg-white rounded-none sm:rounded-xl md:rounded-2xl shadow-2xl w-full h-full sm:h-auto sm:max-w-4xl sm:max-h-[95vh] md:max-h-[90vh] overflow-hidden flex flex-col"
            >
                {{-- Modal Header --}}
                <div class="px-4 sm:px-4 md:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-[color:var(--brand-from)]/5 via-[color:var(--brand-via)]/5 to-[color:var(--brand-to)]/5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-3">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-[color:var(--brand-via)]"></i>
                            <span class="text-sm sm:text-base">{{ tr('Choose Location from Map') }}</span>
                        </h3>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button
                                @click="getCurrentLocation()"
                                x-bind:disabled="isGettingLocation"
                                class="flex-1 sm:flex-none px-3 sm:px-4 py-2 rounded-lg sm:rounded-xl border border-green-500 bg-white text-green-600 font-semibold
                                       hover:bg-green-500 hover:text-white transition-all duration-200
                                       flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed text-xs sm:text-sm"
                            >
                                <i x-show="!isGettingLocation" class="fas fa-location-arrow"></i>
                                <i x-show="isGettingLocation" class="fas fa-spinner fa-spin"></i>
                                <span x-text="isGettingLocation ? '{{ tr('Getting Location...') }}' : '{{ tr('Use My Location') }}'" class="hidden sm:inline"></span>
                                <span x-text="isGettingLocation ? '{{ tr('Loading...') }}' : '{{ tr('My Location') }}'" class="sm:hidden"></span>
                            </button>
                            <button
                                @click="closeModal()"
                                class="p-2 rounded-lg sm:rounded-xl hover:bg-gray-100 transition text-gray-500 hover:text-gray-700 flex-shrink-0"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Map Container --}}
                <div class="flex-1 relative min-h-[calc(100vh-200px)] sm:min-h-[400px]">
                    <div id="mapPicker" class="w-full h-full rounded-b-none sm:rounded-b-xl md:rounded-b-2xl" style="min-height: calc(100vh - 200px);"></div>
                    
                    {{-- Instructions --}}
                    <div class="absolute top-2 sm:top-4 left-1/2 -translate-x-1/2 z-[1000] max-w-[90%] sm:max-w-md">
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg sm:rounded-xl px-3 sm:px-4 py-2 shadow-lg border border-gray-200">
                            <p class="text-xs sm:text-sm text-gray-700 flex items-center justify-center gap-2">
                                <i class="fas fa-info-circle text-[color:var(--brand-via)] text-xs sm:text-sm"></i>
                                <span class="hidden sm:inline">{{ tr('Click on the map to select location') }}</span>
                                <span class="sm:hidden">{{ tr('Tap to select') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Selected Coordinates Display --}}
                    <div
                        x-show="selectedLat && selectedLng"
                        x-cloak
                        class="absolute bottom-2 sm:bottom-4 left-2 sm:left-4 right-2 sm:right-4 z-[1000]"
                    >
                        <div class="bg-white/95 backdrop-blur-sm rounded-lg sm:rounded-xl px-3 sm:px-4 py-2 sm:py-3 shadow-lg border border-gray-200">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-4">
                                <div class="flex-1 w-full sm:w-auto">
                                    <div class="text-[10px] sm:text-xs text-gray-500 mb-1">{{ tr('Selected Location') }}</div>
                                    <div class="text-xs sm:text-sm font-semibold text-gray-900 break-all sm:break-normal">
                                        <span x-text="selectedLat"></span>, <span x-text="selectedLng"></span>
                                    </div>
                                </div>
                                <button
                                    @click="confirmSelection()"
                                    x-bind:disabled="isLoading"
                                    class="w-full sm:w-auto px-4 sm:px-6 py-2 rounded-xl bg-gradient-to-r from-[color:var(--brand-from)] via-[color:var(--brand-via)] to-[color:var(--brand-to)] 
                                           text-white font-semibold hover:shadow-lg transition-all duration-200
                                           flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                                >
                                    <i x-show="!isLoading" class="fas fa-check"></i>
                                    <i x-show="isLoading" class="fas fa-spinner fa-spin"></i>
                                    <span x-text="isLoading ? '{{ tr('Loading...') }}' : '{{ tr('Confirm') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>
