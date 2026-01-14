<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
            {{ tr('Edit Email Template') }}
        </h1>

        <a href="{{ route('saas.emails.index', ['tab' => 'templates']) }}"
           class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 flex items-center justify-center gap-2 text-sm sm:text-base">
            <i class="fas fa-arrow-left"></i>
            <span>{{ tr('Back') }}</span>
        </a>
    </div>

    <form wire:submit.prevent="save" class="space-y-4">
        <x-ui.card>
            <div class="space-y-4">
                {{-- Name --}}
                <x-ui.input
                    wire:model="name"
                    label="{{ tr('Template Name') }}"
                    required
                    placeholder="{{ tr('Enter template name') }}"
                />

                {{-- Type --}}
                <x-ui.select
                    wire:model="type"
                    label="{{ tr('Template Type') }}"
                    required
                >
                    <option value="">{{ tr('Select type') }}</option>
                    <option value="subscription_expiry">{{ tr('Subscription Expiry') }}</option>
                    <option value="subscription_anniversary">{{ tr('Subscription Anniversary') }}</option>
                    <option value="update_notification">{{ tr('Update Notification') }}</option>
                    <option value="greeting">{{ tr('Greeting') }}</option>
                    <option value="user_welcome">{{ tr('User Welcome') }}</option>
                    <option value="new_year_greeting">{{ tr('New Year Greeting') }}</option>
                    <option value="holiday_greeting">{{ tr('Holiday Greeting') }}</option>
                    <option value="custom">{{ tr('Custom') }}</option>
                </x-ui.select>

                {{-- Subject --}}
                <x-ui.input
                    wire:model="subject"
                    label="{{ tr('Email Subject') }}"
                    required
                    placeholder="{{ tr('Enter email subject') }}"
                    hint="{{ tr('You can use variables like {company_name} or {{company_name}}, {expiry_date} or {{expiry_date}}, etc.') }}"
                />

                {{-- Body --}}
                <div>
                    <x-ui.textarea
                        wire:model="body"
                        label="{{ tr('Email Body') }}"
                        required
                        rows="10"
                        placeholder="{{ tr('Enter email body (HTML supported)') }}"
                        hint="{{ tr('You can use HTML tags and variables like {company_name} or {{company_name}}, {expiry_date} or {{expiry_date}}, etc.') }}"
                    />
                </div>

                {{-- Variables Card --}}
                @if(count($variables) > 0)
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        {{-- Card Header (Clickable) --}}
                        <button
                            type="button"
                            onclick="toggleVariablesCard()"
                            class="w-full flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 transition-colors duration-200"
                        >
                            <div class="flex items-center gap-3">
                                <i class="fas fa-code text-blue-600"></i>
                                <h3 class="text-base font-semibold text-gray-800">
                                    {{ tr('Available Variables') }}
                                </h3>
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                    {{ count($variables) }}
                                </span>
                            </div>
                            <i id="variablesCardIcon" class="fas fa-chevron-down text-gray-600 transition-transform duration-300"></i>
                        </button>

                        {{-- Card Content (Collapsible) --}}
                        <div id="variablesCardContent" class="hidden border-t border-gray-200 bg-white">
                            <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
                                @php
                                    $descriptions = $this->getVariableDescriptions();
                                @endphp
                                @foreach($variables as $variable)
                                    <div class="flex flex-col sm:flex-row sm:items-start gap-2 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                <code class="px-2 py-1 bg-white border border-gray-300 rounded text-sm font-mono text-blue-700">
                                                    @{{ $variable }}
                                                </code>
                                                <span class="text-xs text-gray-500">{{ tr('or') }}</span>
                                                <code class="px-2 py-1 bg-white border border-gray-300 rounded text-sm font-mono text-blue-700">
                                                    {{{{ $variable }}}}
                                                </code>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $descriptions[$variable] ?? tr('Variable description not available') }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            onclick="copyVariable('{{ $variable }}', this)"
                                            class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs font-semibold transition-colors flex items-center gap-1"
                                            title="{{ tr('Copy variable') }}"
                                        >
                                            <i class="fas fa-copy"></i>
                                            <span>{{ tr('Copy') }}</span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Is Active --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300">
                    <label for="is_active" class="text-sm font-semibold text-gray-700">
                        {{ tr('Active') }}
                    </label>
                </div>
            </div>
        </x-ui.card>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <x-ui.secondary-button
                href="{{ route('saas.emails.index', ['tab' => 'templates']) }}"
            >
                {{ tr('Cancel') }}
            </x-ui.secondary-button>
            <x-ui.primary-button
                type="submit"
                wire:loading.attr="disabled"
            >
                <i class="fas fa-save"></i>
                <span class="ms-2" wire:loading.remove wire:target="save">{{ tr('Update Template') }}</span>
                <span class="ms-2" wire:loading wire:target="save">{{ tr('Updating...') }}</span>
            </x-ui.primary-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleVariablesCard() {
        const content = document.getElementById('variablesCardContent');
        const icon = document.getElementById('variablesCardIcon');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        } else {
            content.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
    }

    function copyVariable(variable, buttonElement) {
        // Copy both formats
        const textToCopy = `{{${variable}}}`;
        
        navigator.clipboard.writeText(textToCopy).then(function() {
            // Show success message
            if (buttonElement) {
                const originalHTML = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fas fa-check"></i> <span>{{ tr("Copied!") }}</span>';
                buttonElement.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                buttonElement.classList.add('bg-green-600', 'hover:bg-green-700');
                
                setTimeout(function() {
                    buttonElement.innerHTML = originalHTML;
                    buttonElement.classList.remove('bg-green-600', 'hover:bg-green-700');
                    buttonElement.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }, 2000);
            }
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            alert('{{ tr("Failed to copy variable") }}');
        });
    }
</script>
@endpush
