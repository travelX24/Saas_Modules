<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
            {{ tr('Send Email') }}
        </h1>

        <a href="{{ route('saas.emails.index', ['tab' => 'emails']) }}"
           class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 flex items-center justify-center gap-2 text-sm sm:text-base">
            <i class="fas fa-arrow-left"></i>
            <span>{{ tr('Back') }}</span>
        </a>
    </div>

    <form wire:submit.prevent="send" class="space-y-4" id="send-email-form">
        <x-ui.card>
            <div class="space-y-4">
                {{-- Template Selection - Full Width --}}
                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <x-ui.select
                            wire:model.live="templateId"
                            label="{{ tr('Email Template') }}"
                            required
                        >
                            <option value="">{{ tr('Select template') }}</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </x-ui.select>
                        @error('templateId')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if($templateId)
                        @php
                            $selectedTemplate = $templates->firstWhere('id', $templateId);
                        @endphp
                        @if($selectedTemplate)
                            <div class="relative group mb-6">
                                <button 
                                    type="button"
                                    class="p-2.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    aria-label="{{ tr('Template Preview') }}"
                                    title="{{ tr('Hover to preview template') }}"
                                >
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                                
                                {{-- Tooltip/Popover on Hover --}}
                                {{-- LTR: أسفل يمين | RTL: أسفل يسار --}}
                                <div class="absolute right-0 top-full mt-2 rtl:right-auto rtl:left-0 w-80 sm:w-96 bg-white border border-gray-200 rounded-xl shadow-2xl p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 transform group-hover:translate-y-0 -translate-y-2 pointer-events-none group-hover:pointer-events-auto">
                                    <div class="absolute -top-1 right-6 rtl:right-auto rtl:left-6 transform rotate-45 w-3 h-3 bg-white border-r border-b border-gray-200"></div>
                                    <div class="relative bg-white rounded-xl">
                                        <div class="text-sm">
                                            <div class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                                                <i class="fas fa-file-alt text-blue-600"></i>
                                                <span>{{ tr('Template Preview') }}</span>
                                            </div>
                                            <div class="text-gray-700 mb-3 pb-3 border-b border-gray-100">
                                                <strong class="text-gray-900 text-xs">{{ tr('Subject') }}:</strong> 
                                                <div class="text-sm mt-1 text-gray-800">{{ $selectedTemplate->subject }}</div>
                                            </div>
                                            <div class="text-gray-600 text-xs max-h-48 overflow-y-auto pt-2 leading-relaxed">
                                                {!! nl2br(e(strip_tags($selectedTemplate->body))) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Two Columns Layout --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Send Type --}}
                    <x-ui.select
                        wire:model="sendType"
                        label="{{ tr('Send Type') }}"
                        required
                    >
                        <option value="immediate">{{ tr('Send Immediately') }}</option>
                        <option value="scheduled">{{ tr('Schedule for Later') }}</option>
                    </x-ui.select>

                    {{-- Recipient Type --}}
                    <x-ui.select
                        wire:model.live="recipientType"
                        label="{{ tr('Recipient Type') }}"
                        required
                    >
                        <option value="single">{{ tr('Single Recipient') }}</option>
                        <option value="multiple">{{ tr('Multiple Companies') }}</option>
                    </x-ui.select>
                </div>

                {{-- Scheduled At (Full Width) --}}
                @if($sendType === 'scheduled')
                    <x-ui.input
                        wire:model="scheduledAt"
                        type="datetime-local"
                        label="{{ tr('Scheduled Date & Time') }}"
                        required
                    />
                @endif

                {{-- Single Recipient Company (Full Width) --}}
                @if($recipientType === 'single')
                    <x-ui.select
                        wire:model.live="recipientCompanyId"
                        label="{{ tr('Recipient Company') }}"
                        required
                    >
                        <option value="">{{ tr('Select a company') }}</option>
                        @foreach($companies as $company)
                            @php
                                $companyName = app()->getLocale() === 'ar' 
                                    ? $company->legal_name_ar 
                                    : ($company->legal_name_en ?: $company->legal_name_ar);
                            @endphp
                            <option value="{{ $company->id }}">{{ $companyName }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('recipientCompanyId')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                @endif

                {{-- Multiple Companies --}}
                @if($recipientType === 'multiple')
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ tr('Select Companies') }}
                            <span class="text-red-500 ms-1">*</span>
                        </label>
                        <div class="border border-gray-200 rounded-xl p-4 max-h-60 overflow-y-auto">
                            @foreach($companies as $company)
                                @php
                                    // Get company admin email for display
                                    $admin = $company->users()->whereHas('roles', function ($q) {
                                        $q->where('name', 'company-admin');
                                    })->first();
                                    
                                    if (!$admin) {
                                        $admin = $company->users()->first();
                                    }
                                    
                                    $companyName = app()->getLocale() === 'ar' 
                                        ? $company->legal_name_ar 
                                        : ($company->legal_name_en ?: $company->legal_name_ar);
                                @endphp
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="selectedCompanyIds" 
                                        value="{{ $company->id }}"
                                        class="rounded border-gray-300"
                                    >
                                    <span class="text-sm text-gray-700">
                                        {{ $companyName }}
                                        @if($admin && $admin->email)
                                            <span class="text-gray-500 text-xs">({{ tr('Admin') }}: {{ $admin->email }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedCompanyIds')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                {{-- Variables Data --}}
                @if($templateId && count($variablesData) > 0)
                    <div class="border-t border-gray-200 pt-4">
                        <div class="mb-3">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">{{ tr('Template Variables') }}</h3>
                            <p class="text-xs text-gray-500">
                                {{ tr('Values are automatically filled from company data. You can modify them if needed.') }}
                            </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($variablesData as $variable => $value)
                                @php
                                    $variableLabel = tr(ucfirst(str_replace('_', ' ', $variable)));
                                    $autoFilled = in_array($variable, ['company_name', 'admin_name', 'expiry_date', 'days_remaining']);
                                    $hints = [
                                        'company_name' => tr('Auto-filled from selected company'),
                                        'admin_name' => tr('Auto-filled from company admin'),
                                        'expiry_date' => tr('Auto-filled from subscription end date'),
                                        'days_remaining' => tr('Auto-filled from subscription days'),
                                        'welcome_message' => tr('Enter a custom welcome message'),
                                    ];
                                    $hint = $hints[$variable] ?? tr('Enter value for') . ' {' . $variable . '}';
                                @endphp
                                <x-ui.input
                                    wire:model="variablesData.{{ $variable }}"
                                    label="{{ $variableLabel }}"
                                    placeholder="{{ tr('Enter value for') }} @{{ $variable }}"
                                    hint="{{ $hint }}"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <x-ui.secondary-button
                href="{{ route('saas.emails.index', ['tab' => 'emails']) }}"
            >
                {{ tr('Cancel') }}
            </x-ui.secondary-button>
            <x-ui.primary-button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="send"
                onclick="console.log('Send button clicked');"
            >
                <span wire:loading.remove wire:target="send">
                    <i class="fas fa-paper-plane"></i>
                    <span class="ms-2">
                        {{ $sendType === 'immediate' ? tr('Send Now') : tr('Schedule Email') }}
                    </span>
                </span>
                <span wire:loading wire:target="send">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span class="ms-2">{{ tr('Sending...') }}</span>
                </span>
            </x-ui.primary-button>
        </div>
    </form>

    {{-- Display validation errors --}}
    @if ($errors->any())
        <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="text-sm font-semibold text-red-900 mb-2">{{ tr('Please fix the following errors') }}:</div>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('send-email-form');
        const submitButton = form?.querySelector('button[type="submit"]');
        
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                console.log('Submit button clicked');
                console.log('Form data:', {
                    templateId: @js($templateId),
                    recipientCompanyId: @js($recipientCompanyId),
                    sendType: @js($sendType),
                    recipientType: @js($recipientType),
                });
            });
        }
        
        form?.addEventListener('submit', function(e) {
            console.log('Form submitted');
        });
    });

    document.addEventListener('livewire:init', () => {
        console.log('Livewire initialized');
        
        Livewire.on('validation-failed', (errors) => {
            console.log('Validation failed:', errors);
        });
        
        Livewire.on('error', (error) => {
            console.error('Livewire error:', error);
        });
        
        Livewire.hook('message.processed', (message, component) => {
            console.log('Livewire message processed:', message);
        });
    });
</script>
