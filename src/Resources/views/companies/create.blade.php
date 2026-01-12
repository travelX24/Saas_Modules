<div class="space-y-3 sm:space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
            {{ tr('Add Company') }}
        </h1>

        @php
            $isRtl = in_array(app()->getLocale(), ['ar','fa','ur']) || (config('app.rtl') === true);
        @endphp
        <a href="{{ route('saas.companies.index') }}"
           class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 flex items-center justify-center gap-2 text-sm sm:text-base">
            @if($isRtl)
                <i class="fas fa-arrow-right"></i>
            @else
                <i class="fas fa-arrow-left"></i>
            @endif
            <span>{{ tr('Back') }}</span>
        </a>
    </div>

    @php
        $steps = [
            1 => tr('Basic Information'),
            2 => tr('Address & Contact'),
            3 => tr('Additional Info'),
            4 => tr('Documents'),
        ];
    @endphp

    {{-- ✅ Single Card --}}
    <form wire:submit.prevent="store" class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        
        {{-- Confirmation Dialog --}}
        <x-ui.confirm-dialog
            id="save-company"
            :title="tr('Confirm Save Company')"
            :message="tr('Are you sure you want to save this company? Please review all information before proceeding.')"
            :confirmText="tr('Yes, Save Company')"
            :cancelText="tr('Cancel')"
            confirmAction="wire:store"
            type="info"
            icon="fa-building"
        />

        {{-- Stepper --}}
        <div class="px-3 sm:px-4 md:px-6 py-4 sm:py-5 bg-gray-50/40">
            {{-- Desktop / Tablet --}}
            <div class="hidden sm:block">
                <div class="flex justify-center overflow-x-auto">
                    <div class="inline-flex items-start justify-center min-w-fit">
                        @foreach($steps as $stepNum => $stepLabel)
                            @php
                                $isActive = ($tab == $stepNum);
                                $isCompleted = ($tab > $stepNum);
                                $isLast = $loop->last;
                            @endphp

                            <button type="button"
                            wire:click="goToTab({{ $stepNum }})"
                            wire:loading.attr="disabled"
                            wire:target="goToTab"
                            class="group flex flex-col items-center gap-1 px-1 disabled:opacity-50 disabled:cursor-wait">
                                <div class="relative w-10 sm:w-12 h-10 sm:h-12 transition-transform duration-200 group-hover:scale-105">
                                    <svg viewBox="0 0 56 56" class="absolute inset-0">
                                        <polygon points="28,4 48,20 40,48 16,48 8,20"
                                            fill="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#f3f4f6') }}"
                                            stroke="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#d1d5db') }}"
                                            stroke-width="2"/>
                                    </svg>

                                    <div class="absolute inset-0 flex items-center justify-center text-sm sm:text-base font-extrabold
                                        {{ $isActive || $isCompleted ? 'text-white' : 'text-gray-700' }}">
                                        {{ $isCompleted ? '✓' : $stepNum }}
                                    </div>
                                </div>

                                <div class="text-[10px] sm:text-[11px] font-semibold text-center leading-4 max-w-[100px] sm:max-w-[120px]
                                    {{ $isActive || $isCompleted ? 'text-[color:var(--brand-via)]' : 'text-gray-500' }}">
                                    {{ $stepLabel }}
                                </div>
                            </button>

                            @if(! $isLast)
                                <div class="h-[3px] w-12 sm:w-16 md:w-24 mx-2 sm:mx-4 mt-6 rounded-full
                                    {{ $tab > $stepNum ? 'bg-[color:var(--brand-via)]' : 'bg-gray-200' }}">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-3">
                    <span class="text-xs sm:text-sm font-semibold text-[color:var(--brand-via)]">
                        {{ tr('Step') }} {{ $tab }} {{ tr('of') }} 4: {{ $steps[$tab] ?? '' }}
                    </span>
                </div>
            </div>

            {{-- Mobile --}}
            <div class="sm:hidden">
                <div class="flex justify-center">
                    <div class="inline-flex items-center gap-2">
                        @foreach($steps as $stepNum => $stepLabel)
                            @php
                                $isActive = ($tab == $stepNum);
                                $isCompleted = ($tab > $stepNum);
                                $isLast = $loop->last;
                            @endphp

                            <button type="button" wire:click="goToTab({{ $stepNum }})" wire:loading.attr="disabled" wire:target="goToTab" class="group disabled:opacity-50 disabled:cursor-wait">
                                <div class="relative w-10 h-10">
                                    <svg viewBox="0 0 56 56" class="absolute inset-0">
                                        <polygon points="28,4 48,20 40,48 16,48 8,20"
                                            fill="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#f3f4f6') }}"
                                            stroke="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#d1d5db') }}"
                                            stroke-width="2"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center text-sm font-extrabold
                                        {{ $isActive || $isCompleted ? 'text-white' : 'text-gray-700' }}">
                                        {{ $isCompleted ? '✓' : $stepNum }}
                                    </div>
                                </div>
                            </button>

                            @if(! $isLast)
                                <div class="h-[3px] w-7 rounded-full
                                    {{ $tab > $stepNum ? 'bg-[color:var(--brand-via)]' : 'bg-gray-200' }}">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-3 text-sm font-semibold text-[color:var(--brand-via)]">
                    {{ tr('Step') }} {{ $tab }} / 4 — {{ $steps[$tab] ?? '' }}
                </div>
            </div>
        </div>

        {{-- Content (✅ بدون scroll داخلي) --}}
        <div class="p-3 sm:p-4 md:p-6">
            @if($tab === 1)
                @include('saas::companies.partials.tab-basic')
            @elseif($tab === 2)
                @include('saas::companies.partials.tab-address')
            @elseif($tab === 3)
                @include('saas::companies.partials.tab-additional')
            @elseif($tab === 4)
                @include('saas::companies.partials.tab-documents')
            @endif
        </div>

        {{-- Actions --}}
        <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 border-t border-gray-100 bg-white">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-center sm:justify-between">
                <div class="w-full sm:w-auto sm:inline-flex">
                    <x-ui.secondary-button
                        type="button"
                        wire:click="prevTab"
                        :disabled="$tab === 1"
                        :fullWidth="true"
                        :arrow="true"
                        arrowDirection="left"
                        class="{{ $tab === 1 ? 'opacity-40 cursor-not-allowed' : '' }}"
                    >
                        {{ tr('Previous') }}
                    </x-ui.secondary-button>
                </div>

                <div class="w-full sm:w-auto sm:inline-flex">
                    @if($tab < 4)
                        <x-ui.primary-button
                            type="button"
                            wire:click="nextTab"
                            :fullWidth="true"
                            wire:loading.attr="disabled"
                            wire:target="nextTab"
                        >
                            <span wire:loading.remove wire:target="nextTab">
                                {{ tr('Next') }}
                            </span>
                            <span wire:loading wire:target="nextTab" class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>{{ tr('Validating...') }}</span>
                            </span>
                        </x-ui.primary-button>
                    @else
                        <div wire:loading.class="opacity-50 pointer-events-none" wire:target="store">
                            <x-ui.primary-button
                                type="button"
                                :arrow="false"
                                :fullWidth="true"
                                wire:loading.attr="disabled"
                                wire:target="store"
                                x-on:click="$dispatch('open-confirm-save-company')"
                                class="disabled:opacity-50 disabled:cursor-wait"
                            >
                                <span wire:loading.remove wire:target="store" class="flex items-center gap-2">
                                    <i class="fas fa-save"></i>
                                    <span>{{ tr('Save Company') }}</span>
                                </span>
                                <span wire:loading wire:target="store" class="flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>{{ tr('Saving...') }}</span>
                                </span>
                            </x-ui.primary-button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
