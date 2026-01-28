@php
    $steps = [
        1 => tr('Basic Information'),
        2 => tr('Address & Contact'),
        3 => tr('Additional Info'),
        4 => tr('Documents'),
    ];
@endphp

<div>
    {{-- Confirmation Dialog --}}
    <x-ui.confirm-dialog
        id="update-company"
        :title="tr('Confirm Update Company')"
        :message="tr('Are you sure you want to update this company? Please review all information before proceeding.')"
        :confirmText="tr('Yes, Update Company')"
        :cancelText="tr('Cancel')"
        confirmAction="wire:update"
        type="info"
        icon="fa-building"
    />

    {{-- Stepper --}}
    <div class="px-6 py-5 bg-gradient-to-br from-gray-50 via-gray-50/80 to-gray-50/60 border-b border-gray-200/50 mb-4">
        {{-- Desktop / Tablet --}}
        <div class="hidden sm:block">
            <div class="flex justify-center overflow-x-auto pb-2">
                <div class="inline-flex items-start justify-center min-w-fit">
                    @foreach($steps as $stepNum => $stepLabel)
                        @php
                            $isActive = ($tab == $stepNum);
                            $isCompleted = ($tab > $stepNum);
                            $isLast = $loop->last;
                        @endphp
                        <button 
                            type="button"
                            wire:click="goToTab({{ $stepNum }})"
                            wire:loading.attr="disabled"
                            wire:target="goToTab"
                            class="group flex flex-col items-center gap-2 px-1 transition-all duration-200 disabled:opacity-50 disabled:cursor-wait {{ $tab === $stepNum ? 'scale-105' : 'hover:scale-105' }}"
                        >
                            <div class="relative w-12 h-12 transition-all duration-200">
                                <svg viewBox="0 0 56 56" class="absolute inset-0 drop-shadow-sm">
                                    <polygon 
                                        points="28,4 48,20 40,48 16,48 8,20"
                                        fill="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#f3f4f6') }}"
                                        stroke="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#d1d5db') }}"
                                        stroke-width="2.5"
                                        class="transition-all duration-200"
                                    />
                                </svg>
                                <div 
                                    class="absolute inset-0 flex items-center justify-center text-base font-extrabold transition-colors duration-200 {{ $isActive || $isCompleted ? 'text-white' : 'text-gray-600' }}"
                                >
                                    {{ $isCompleted ? '✓' : $stepNum }}
                                </div>
                            </div>

                            <div 
                                class="text-[11px] font-semibold text-center leading-tight max-w-[110px] transition-colors duration-200 {{ $isActive || $isCompleted ? 'text-[color:var(--brand-via)]' : 'text-gray-500' }}"
                            >
                                {{ $stepLabel }}
                            </div>
                        </button>

                        @if(!$isLast)
                            <div 
                                class="h-[3px] w-16 md:w-20 lg:w-24 mx-3 md:mx-4 mt-6 rounded-full transition-all duration-300 {{ $tab > $stepNum ? 'bg-gradient-to-r from-[color:var(--brand-via)] to-[color:var(--brand-via)]/80 shadow-sm' : 'bg-gray-200' }}"
                            ></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="text-center mt-4">
                <span class="text-sm font-bold text-[color:var(--brand-via)] bg-white/60 px-4 py-1.5 rounded-full inline-block shadow-sm">
                    {{ tr('Step') }} {{ $tab }} {{ tr('of') }} 4: {{ $steps[$tab] ?? '' }}
                </span>
            </div>
        </div>

        {{-- Mobile --}}
        <div class="sm:hidden">
            <div class="flex justify-center overflow-x-auto pb-2">
                <div class="inline-flex items-center gap-2">
                    @foreach($steps as $stepNum => $stepLabel)
                        @php
                            $isActive = ($tab == $stepNum);
                            $isCompleted = ($tab > $stepNum);
                            $isLast = $loop->last;
                        @endphp
                        <button 
                            type="button"
                            wire:click="goToTab({{ $stepNum }})"
                            wire:loading.attr="disabled"
                            wire:target="goToTab"
                            class="group transition-all duration-200 disabled:opacity-50 disabled:cursor-wait {{ $tab === $stepNum ? 'scale-110' : '' }}"
                        >
                            <div class="relative w-10 h-10">
                                <svg viewBox="0 0 56 56" class="absolute inset-0">
                                    <polygon 
                                        points="28,4 48,20 40,48 16,48 8,20"
                                        fill="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#f3f4f6') }}"
                                        stroke="{{ $isActive ? 'var(--brand-via)' : ($isCompleted ? 'var(--brand-via)' : '#d1d5db') }}"
                                        stroke-width="2.5"
                                    />
                                </svg>
                                <div 
                                    class="absolute inset-0 flex items-center justify-center text-sm font-extrabold {{ $isActive || $isCompleted ? 'text-white' : 'text-gray-600' }}"
                                >
                                    {{ $isCompleted ? '✓' : $stepNum }}
                                </div>
                            </div>
                        </button>

                        @if(!$isLast)
                            <div 
                                class="h-[3px] w-7 rounded-full transition-all duration-300 {{ $tab > $stepNum ? 'bg-[color:var(--brand-via)]' : 'bg-gray-200' }}"
                            ></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="text-center mt-3">
                <span class="text-xs font-bold text-[color:var(--brand-via)] bg-white/60 px-3 py-1 rounded-full inline-block">
                    {{ tr('Step') }} {{ $tab }} / 4 — {{ $steps[$tab] ?? '' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <form wire:submit.prevent="update" class="w-full">
        <div class="p-3 sm:p-4 md:p-6">
            @if($tab === 1)
                <div wire:key="tab-basic-edit">
                    @include('saas::companies.partials.tab-basic', ['isEditMode' => true])
                </div>
            @elseif($tab === 2)
                <div wire:key="tab-address-edit">
                    @include('saas::companies.partials.tab-address')
                </div>
            @elseif($tab === 3)
                <div wire:key="tab-additional-edit">
                    @include('saas::companies.partials.tab-additional')
                </div>
            @elseif($tab === 4)
                <div wire:key="tab-documents-edit">
                    @include('saas::companies.partials.tab-documents')
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-center sm:justify-between mt-4 px-6">
            <div class="w-full sm:w-auto sm:inline-flex">
                <x-ui.secondary-button
                    type="button"
                    wire:click="goToTab({{ max(1, $tab - 1) }})"
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
                        wire:click="goToTab({{ min(4, $tab + 1) }})"
                        :fullWidth="true"
                        wire:loading.attr="disabled"
                        wire:target="goToTab"
                    >
                        <span wire:loading.remove wire:target="goToTab">
                            {{ tr('Next') }}
                        </span>
                        <span wire:loading wire:target="goToTab" class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>{{ tr('Validating...') }}</span>
                        </span>
                    </x-ui.primary-button>
                @else
                    <div wire:loading.class="opacity-50 pointer-events-none" wire:target="update">
                        <x-ui.primary-button
                            type="button"
                            :arrow="false"
                            :fullWidth="true"
                            wire:loading.attr="disabled"
                            wire:target="update"
                            x-on:click="$dispatch('open-confirm-update-company')"
                            class="disabled:opacity-50 disabled:cursor-wait"
                        >
                            <span wire:loading.remove wire:target="update" class="flex items-center gap-2">
                                <i class="fas fa-save"></i>
                                <span>{{ tr('Update Company') }}</span>
                            </span>
                            <span wire:loading wire:target="update" class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>{{ tr('Updating...') }}</span>
                            </span>
                        </x-ui.primary-button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
