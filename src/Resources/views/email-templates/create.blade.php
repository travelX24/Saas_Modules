<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
            {{ tr('Create Email Template') }}
        </h1>

        <a href="{{ route('saas.emails.index', ['tab' => 'templates']) }}"
           class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 flex items-center justify-center gap-2 text-sm sm:text-base">
            <i class="fas fa-arrow-left"></i>
            <span>{{ tr('Back') }}</span>
        </a>
    </div>

</div>

@if (session('success'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        {{ session('error') }}
    </div>
@endif

<form wire:submit.prevent="save" class="space-y-4" id="create-template-form">

        <x-ui.card>
            <div class="space-y-4">
                {{-- Name --}}
                <x-ui.input
                    wire:model="name"
                    label="{{ tr('Template Name') }}"
                    required
                    placeholder="{{ tr('Enter template name') }}"
                />
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                {{-- Type --}}
                <x-ui.select
                    wire:model.live="type"
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
                @error('type')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                {{-- Subject --}}
                <x-ui.input
                    wire:model="subject"
                    label="{{ tr('Email Subject') }}"
                    required
                    placeholder="{{ tr('Enter email subject') }}"
                    hint="{{ tr('You can use variables like {company_name}, {expiry_date}, etc.') }}"
                />
                @error('subject')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

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
                @error('body')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

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
            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                onclick="console.log('Button clicked - Livewire should handle this');"
                class="px-4 py-2 bg-[color:var(--brand-via)] text-white rounded-2xl hover:bg-[color:var(--brand-to)] flex items-center justify-center gap-2 text-sm sm:text-base font-semibold transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-save"></i>
                <span class="ms-2" wire:loading.remove wire:target="save">{{ tr('Save Template') }}</span>
                <span class="ms-2" wire:loading wire:target="save">{{ tr('Saving...') }}</span>
            </button>
        </div>
    </form>
</div>

