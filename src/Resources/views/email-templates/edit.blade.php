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
                    hint="{{ tr('You can use variables like {company_name}, {expiry_date}, etc.') }}"
                />

                {{-- Body --}}
                <div>
                    <x-ui.textarea
                        wire:model="body"
                        label="{{ tr('Email Body') }}"
                        required
                        rows="10"
                        placeholder="{{ tr('Enter email body (HTML supported)') }}"
                        hint="{{ tr('You can use HTML tags and variables like {company_name}, {expiry_date}, etc.') }}"
                    />
                </div>

                {{-- Available Variables --}}
                @if(count($variables) > 0)
                    <div class="bg-gray-50 rounded-xl p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">
                            {{ tr('Available Variables') }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($variables as $variable)
                                <span class="px-2 py-1 bg-white border border-gray-200 rounded text-xs font-mono text-gray-700">
                                    @{{ $variable }}
                                </span>
                            @endforeach
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
