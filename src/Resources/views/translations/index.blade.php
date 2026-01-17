<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    @php
        $locale = app()->getLocale();
        $isRtl = in_array(substr($locale, 0, 2), ['ar', 'fa', 'ur', 'he']);
    @endphp
    <x-ui.page-header
        :title="tr('Translations')"
        :subtitle="tr('Manage and edit translations for English and Arabic')"
    >
        <x-slot:action>
            <div class="flex items-center gap-2 {{ $isRtl ? 'flex-row-reverse' : '' }}">
                <button
                    wire:click="exportTranslations"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 shadow-lg hover:shadow-xl rounded-lg transition-all duration-300"
                >
                    <i class="fas fa-download"></i>
                    {{ tr('Export') }}
                </button>
                <label class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all duration-300 cursor-pointer relative {{ $isRtl ? 'flex-row-reverse' : '' }}"
                       wire:loading.class="opacity-70 cursor-wait"
                       wire:target="importFile,importTranslations">
                    <input type="file" wire:model="importFile" accept=".json" class="hidden" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="importFile,importTranslations" class="inline-flex items-center gap-2">
                        <i class="fas fa-upload"></i>
                        {{ tr('Import') }}
                    </span>
                    <span wire:loading wire:target="importFile,importTranslations" class="flex items-center gap-2 text-white">
                        <i class="fas fa-spinner fa-spin text-amber-300 {{ $isRtl ? 'ms-2' : 'me-2' }}"></i>
                        <span>{{ tr('Importing...') }}</span>
                    </span>
                </label>
            </div>
        </x-slot:action>
    </x-ui.page-header>

    {{-- Search --}}
    <x-ui.card>
        <div class="space-y-4">
            {{-- Search Bar and Per Page Filter --}}
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                <div class="flex-1">
                    <x-ui.search-box
                        model="search"
                        :placeholder="tr('Search by key or text...')"
                        :debounce="300"
                    />
                </div>
                <div class="w-full sm:w-40">
                    <x-ui.select wire:model.live="perPage">
                        <option value="20">20 {{ tr('per page') }}</option>
                        <option value="50">50 {{ tr('per page') }}</option>
                        <option value="100">100 {{ tr('per page') }}</option>
                    </x-ui.select>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Translations Table --}}
    @if($translations->count() > 0)
        <x-ui.card>
            @php
                $locale = app()->getLocale();
                $isRtl = in_array(substr($locale, 0, 2), ['ar', 'fa', 'ur', 'he']);
                $textAlign = $isRtl ? 'text-right' : 'text-left';
                $dir = $isRtl ? 'rtl' : 'ltr';
                $stickyPosition = $isRtl ? 'left' : 'right';
            @endphp
            <style>
                .translations-table-sticky-actions th:last-child,
                .translations-table-sticky-actions td:last-child {
                    position: sticky;
                    {{ $stickyPosition }}: 0;
                    background-color: white;
                    z-index: 10;
                    min-width: 120px;
                }
                .translations-table-sticky-actions thead th:last-child {
                    z-index: 11;
                    background-color: white;
                }
                .translations-table-sticky-actions tbody tr:hover td:last-child {
                    background-color: #f9fafb;
                }
            </style>
            <div class="overflow-x-auto overflow-y-visible translations-table-sticky-actions" dir="{{ $dir }}">
                <table class="w-full" dir="{{ $dir }}">
                    <thead>
                        <tr>
                            <th class="{{ $textAlign }} py-3 px-4 text-sm font-bold text-gray-700">
                                {{ tr('English') }}
                            </th>
                            <th class="{{ $textAlign }} py-3 px-4 text-sm font-bold text-gray-700">
                                {{ tr('Arabic') }}
                            </th>
                            <th class="{{ $textAlign }} py-3 px-4 text-sm font-bold text-gray-700">
                                {{ tr('Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($translations as $translation)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4">
                                    @if(isset($editing[$translation->id]))
                                        <x-ui.textarea
                                            wire:model="editing.{{ $translation->id }}.en"
                                            rows="2"
                                            class="text-sm"
                                            :error="null"
                                        />
                                    @else
                                        <div class="text-sm text-gray-700" dir="ltr">
                                            {{ $translation->en }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if(isset($editing[$translation->id]))
                                        <x-ui.textarea
                                            wire:model="editing.{{ $translation->id }}.ar"
                                            rows="2"
                                            class="text-sm"
                                            dir="rtl"
                                            :error="null"
                                        />
                                    @else
                                        <div class="text-sm text-gray-700" dir="rtl">
                                            {{ $translation->ar }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if(isset($editing[$translation->id]))
                                        <div class="flex items-center gap-2 {{ $isRtl ? 'flex-row-reverse' : '' }}">
                                            <button
                                                wire:click="saveTranslation({{ $translation->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors"
                                            >
                                                <i class="fas fa-check {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
                                                {{ tr('Save') }}
                                            </button>
                                            <button
                                                wire:click="cancelEdit({{ $translation->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                                            >
                                                <i class="fas fa-times {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
                                                {{ tr('Cancel') }}
                                            </button>
                                        </div>
                                    @else
                                        <button
                                            wire:click="startEdit({{ $translation->id }})"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors"
                                        >
                                            <i class="fas fa-edit"></i>
                                            {{ tr('Edit') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($translations->hasPages())
                @php
                    $locale = app()->getLocale();
                    $isRtl = in_array(substr($locale, 0, 2), ['ar', 'fa', 'ur', 'he']);
                @endphp
                <div class="mt-4 flex items-center justify-between border-t border-gray-200 pt-4" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                    <div class="text-sm text-gray-700">
                        {{ tr('Showing') }} {{ $translations->firstItem() }} 
                        {{ tr('to') }} {{ $translations->lastItem() }} 
                        {{ tr('of') }} {{ $translations->total() }} {{ tr('results') }}
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isRtl)
                            {{-- RTL: Next first (appears on right), then Previous (appears on left) --}}
                            @if($translations->hasMorePages())
                                <button
                                    wire:click="nextPage"
                                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 order-3"
                                >
                                    {{ tr('Next') }}
                                    <i class="fas fa-chevron-left me-1"></i>
                                </button>
                            @else
                                <button
                                    disabled
                                    class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed order-3"
                                >
                                    {{ tr('Next') }}
                                    <i class="fas fa-chevron-left me-1"></i>
                                </button>
                            @endif
                            <span class="text-sm text-gray-700 order-2">
                                {{ tr('Page') }} {{ $translations->currentPage() }} {{ tr('of') }} {{ $translations->lastPage() }}
                            </span>
                            @if($translations->onFirstPage())
                                <button
                                    disabled
                                    class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed order-1"
                                >
                                    <i class="fas fa-chevron-right ms-1"></i>
                                    {{ tr('Previous') }}
                                </button>
                            @else
                                <button
                                    wire:click="previousPage"
                                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 order-1"
                                >
                                    <i class="fas fa-chevron-right ms-1"></i>
                                    {{ tr('Previous') }}
                                </button>
                            @endif
                        @else
                            {{-- LTR: Previous first, then Next --}}
                            @if($translations->onFirstPage())
                                <button
                                    disabled
                                    class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed"
                                >
                                    <i class="fas fa-chevron-left me-1"></i>
                                    {{ tr('Previous') }}
                                </button>
                            @else
                                <button
                                    wire:click="previousPage"
                                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    <i class="fas fa-chevron-left me-1"></i>
                                    {{ tr('Previous') }}
                                </button>
                            @endif
                            <span class="text-sm text-gray-700">
                                {{ tr('Page') }} {{ $translations->currentPage() }} {{ tr('of') }} {{ $translations->lastPage() }}
                            </span>
                            @if($translations->hasMorePages())
                                <button
                                    wire:click="nextPage"
                                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    {{ tr('Next') }}
                                    <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            @else
                                <button
                                    disabled
                                    class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-md cursor-not-allowed"
                                >
                                    {{ tr('Next') }}
                                    <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </x-ui.card>
    @else
        <x-ui.card>
            <div class="text-center py-12">
                <i class="fas fa-language text-4xl text-gray-300 mb-4"></i>
                <p class="text-sm font-medium text-gray-900 mb-1">
                    {{ tr('No translations found') }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ tr('Try adjusting your search or filters') }}
                </p>
            </div>
        </x-ui.card>
    @endif
</div>
