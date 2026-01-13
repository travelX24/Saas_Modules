<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
                {{ tr('Email Templates') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ tr('Manage email templates for automated notifications') }}
            </p>
        </div>

        <div class="flex gap-2">
            <x-ui.primary-button
                href="{{ route('saas.emails.scheduled') }}"
                :arrow="false"
                :fullWidth="false"
            >
                <i class="fas fa-paper-plane"></i>
                <span class="ms-2">{{ tr('Send Email') }}</span>
            </x-ui.primary-button>
            <x-ui.primary-button
                href="{{ route('saas.email-templates.create') }}"
                :arrow="false"
                :fullWidth="false"
            >
                <i class="fas fa-plus"></i>
                <span class="ms-2">{{ tr('Add Template') }}</span>
            </x-ui.primary-button>
        </div>
    </div>

    {{-- Search and Filters --}}
    <x-ui.card>
        <div class="space-y-4">
            {{-- Search Bar --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <x-ui.search-box
                    model="search"
                    :placeholder="tr('Search by name or subject...')"
                    :debounce="300"
                />
            </div>

            {{-- Filters --}}
            <div 
                x-data="{ open: @js(true) }"
                class="space-y-3"
            >
                <div class="flex items-center justify-between">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex items-center justify-between text-sm font-semibold text-gray-700 hover:text-gray-900 transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>{{ tr('Filters') }}</span>
                        </span>
                        <i 
                            class="fas fa-chevron-down transition-transform ms-2"
                            :class="open ? 'rotate-180' : ''"
                        ></i>
                    </button>
                </div>

                {{-- Filters Content --}}
                <div 
                    x-show="open"
                    x-transition
                    class="flex flex-col sm:flex-row gap-3 flex-wrap items-end"
                >
                    <x-ui.filter-select
                        model="typeFilter"
                        :label="tr('Type')"
                        :placeholder="tr('All Types')"
                        :options="$types"
                        width="md"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />

                    <x-ui.filter-select
                        model="statusFilter"
                        :label="tr('Status')"
                        :placeholder="tr('All Status')"
                        :options="[
                            ['value' => 'active', 'label' => tr('Active')],
                            ['value' => 'inactive', 'label' => tr('Inactive')],
                        ]"
                        width="md"
                        :defer="false"
                        :applyOnChange="true"
                        allValue="all"
                    />
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Templates List --}}
    @if($templates->count() > 0)
        <x-ui.card class="overflow-visible">
            <x-ui.table :headers="[
                tr('Name'),
                tr('Subject'),
                tr('Type'),
                tr('Status'),
                tr('Created'),
                tr('Actions'),
            ]">
                @foreach($templates as $template)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-3">
                            <div class="font-semibold text-gray-900 text-sm">
                                {{ $template->name }}
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="text-sm text-gray-700 truncate max-w-xs" title="{{ $template->subject }}">
                                {{ $template->subject }}
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <x-ui.badge type="info" size="sm">
                                {{ tr(ucfirst(str_replace('_', ' ', $template->type))) }}
                            </x-ui.badge>
                        </td>
                        <td class="py-3 px-3">
                            <x-ui.badge :type="$template->is_active ? 'success' : 'danger'" size="sm">
                                {{ $template->is_active ? tr('Active') : tr('Inactive') }}
                            </x-ui.badge>
                        </td>
                        <td class="py-3 px-3">
                            <span class="text-sm text-gray-700">
                                {{ $template->created_at->format('Y-m-d') }}
                            </span>
                        </td>
                        <td class="py-3 px-3 relative overflow-visible">
                            <x-ui.dropdown-menu>
                                <x-ui.dropdown-item 
                                    href="{{ route('saas.email-templates.edit', $template->id) }}"
                                >
                                    <i class="fas fa-edit w-4 me-2"></i>
                                    {{ tr('Edit') }}
                                </x-ui.dropdown-item>
                                <x-ui.dropdown-item 
                                    href="{{ route('saas.emails.send', ['templateId' => $template->id]) }}"
                                >
                                    <i class="fas fa-paper-plane w-4 me-2"></i>
                                    {{ tr('Send') }}
                                </x-ui.dropdown-item>
                                <div class="border-t border-gray-100 my-1"></div>
                                <x-ui.dropdown-item 
                                    :class="$template->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50'"
                                    href="#"
                                    wire:click="toggleStatus({{ $template->id }})"
                                >
                                    @if($template->is_active)
                                        <i class="fas fa-pause w-4 me-2"></i>
                                        {{ tr('Deactivate') }}
                                    @else
                                        <i class="fas fa-play w-4 me-2"></i>
                                        {{ tr('Activate') }}
                                    @endif
                                </x-ui.dropdown-item>
                                <div class="border-t border-gray-100 my-1"></div>
                                <x-ui.dropdown-item 
                                    class="text-red-600 hover:bg-red-50"
                                    href="#"
                                    wire:click="deleteTemplate({{ $template->id }})"
                                    wire:confirm="{{ tr('Are you sure you want to delete this template?') }}"
                                >
                                    <i class="fas fa-trash w-4 me-2"></i>
                                    {{ tr('Delete') }}
                                </x-ui.dropdown-item>
                            </x-ui.dropdown-menu>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <x-ui.card>
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-envelope text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    {{ tr('No templates found') }}
                </h3>
                <p class="text-sm text-gray-500 mb-6">
                    @if($search || $typeFilter !== 'all' || $statusFilter !== 'all')
                        {{ tr('Try adjusting your search or filters') }}
                    @else
                        {{ tr('Get started by creating your first email template') }}
                    @endif
                </p>
                <x-ui.primary-button
                    href="{{ route('saas.email-templates.create') }}"
                >
                    <i class="fas fa-plus"></i>
                    <span class="ms-2">{{ tr('Create Template') }}</span>
                </x-ui.primary-button>
            </div>
        </x-ui.card>
    @endif
</div>
