<div class="space-y-4 sm:space-y-6" wire:poll.10s>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[color:var(--brand-via)]">
                {{ tr('Email Messages') }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ tr('Manage email messages and templates') }}
            </p>
        </div>

        <div class="flex gap-2">
            @if($activeTab === 'emails')
                <x-ui.primary-button
                    href="{{ route('saas.emails.send') }}"
                    :arrow="false"
                    :fullWidth="false"
                >
                    <i class="fas fa-plus"></i>
                    <span class="ms-2">{{ tr('Send Email') }}</span>
                </x-ui.primary-button>
            @else
                <x-ui.primary-button
                    href="{{ route('saas.email-templates.create') }}"
                    :arrow="false"
                    :fullWidth="false"
                >
                    <i class="fas fa-plus"></i>
                    <span class="ms-2">{{ tr('Add Template') }}</span>
                </x-ui.primary-button>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <x-ui.card class="p-0">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button
                    wire:click="setActiveTab('emails')"
                    type="button"
                    class="flex-1 sm:flex-none px-4 sm:px-6 py-4 text-sm font-semibold border-b-2 transition-colors duration-200 {{ $activeTab === 'emails' ? 'border-[color:var(--brand-via)] text-[color:var(--brand-via)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <i class="fas fa-envelope me-2"></i>
                    {{ tr('Email Messages') }}
                </button>
                <button
                    wire:click="setActiveTab('templates')"
                    type="button"
                    class="flex-1 sm:flex-none px-4 sm:px-6 py-4 text-sm font-semibold border-b-2 transition-colors duration-200 {{ $activeTab === 'templates' ? 'border-[color:var(--brand-via)] text-[color:var(--brand-via)]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    <i class="fas fa-file-alt me-2"></i>
                    {{ tr('Templates') }}
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-4 sm:p-6">
            {{-- Emails Tab --}}
            @if($activeTab === 'emails')
                <div class="space-y-4 sm:space-y-6">
                    {{-- Filters --}}
                    <div class="flex flex-col sm:flex-row gap-3 flex-wrap items-end">
                        <x-ui.filter-select
                            model="statusFilter"
                            :label="tr('Status')"
                            :placeholder="tr('All Status')"
                            :options="[
                                ['value' => 'pending', 'label' => tr('Pending')],
                                ['value' => 'processing', 'label' => tr('Processing')],
                                ['value' => 'sent', 'label' => tr('Sent')],
                                ['value' => 'failed', 'label' => tr('Failed')],
                            ]"
                            width="md"
                            :defer="false"
                            :applyOnChange="true"
                            allValue="all"
                        />

                        <x-ui.filter-select
                            model="sendTypeFilter"
                            :label="tr('Send Type')"
                            :placeholder="tr('All Types')"
                            :options="[
                                ['value' => 'immediate', 'label' => tr('Immediate')],
                                ['value' => 'scheduled', 'label' => tr('Scheduled')],
                            ]"
                            width="md"
                            :defer="false"
                            :applyOnChange="true"
                            allValue="all"
                        />
                    </div>

                    {{-- Scheduled Emails List --}}
                    @if($scheduledEmails && $scheduledEmails->count() > 0)
                        <x-ui.card class="overflow-visible">
                            <x-ui.table :headers="[
                                tr('Template'),
                                tr('Recipient'),
                                tr('Type'),
                                tr('Scheduled At'),
                                tr('Status'),
                                tr('Actions'),
                            ]">
                                @foreach($scheduledEmails as $scheduledEmail)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-3">
                                            <div class="font-semibold text-gray-900 text-sm">
                                                {{ $scheduledEmail->template->name }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $scheduledEmail->template->subject }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($scheduledEmail->recipient_type === 'single')
                                                @php
                                                    $companyId = $scheduledEmail->recipient_company_ids[0] ?? null;
                                                    $company = $companyId ? \Athka\Saas\Models\SaasCompany::find($companyId) : null;
                                                    if ($company) {
                                                        $admin = $company->users()->whereHas('roles', function ($q) {
                                                            $q->where('name', 'company-admin');
                                                        })->first();
                                                        if (!$admin) {
                                                            $admin = $company->users()->first();
                                                        }
                                                    }
                                                @endphp
                                                <div class="text-sm text-gray-700">
                                                    @if($admin && $admin->email)
                                                        {{ $admin->email }}
                                                    @else
                                                        {{ tr('No admin email found') }}
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-700">
                                                    {{ count($scheduledEmail->recipient_company_ids ?? []) }} {{ tr('companies') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <x-ui.badge type="info" size="sm">
                                                {{ $scheduledEmail->send_type === 'immediate' ? tr('Immediate') : tr('Scheduled') }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="text-sm text-gray-700">
                                                @if($scheduledEmail->scheduled_at)
                                                    {{ $scheduledEmail->scheduled_at->format('Y-m-d') }}
                                                @else
                                                    {{ tr('Immediate') }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'sent' => 'success',
                                                    'failed' => 'danger',
                                                ];
                                                $statusColor = $statusColors[$scheduledEmail->status] ?? 'info';
                                            @endphp
                                            <x-ui.badge :type="$statusColor" size="sm">
                                                {{ tr(ucfirst($scheduledEmail->status)) }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="py-3 px-3 relative overflow-visible">
                                            <x-ui.dropdown-menu>
                                                <x-ui.dropdown-item 
                                                    href="#"
                                                    wire:click="viewEmail({{ $scheduledEmail->id }})"
                                                >
                                                    <i class="fas fa-eye w-4 me-2"></i>
                                                    {{ tr('View') }}
                                                </x-ui.dropdown-item>
                                                @if($scheduledEmail->status === 'pending')
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <x-ui.dropdown-item 
                                                        class="text-red-600 hover:bg-red-50"
                                                        href="#"
                                                        wire:click="cancelScheduled({{ $scheduledEmail->id }})"
                                                        wire:confirm="{{ tr('Are you sure you want to cancel this scheduled email?') }}"
                                                    >
                                                        <i class="fas fa-times w-4 me-2"></i>
                                                        {{ tr('Cancel') }}
                                                    </x-ui.dropdown-item>
                                                @endif
                                            </x-ui.dropdown-menu>
                                        </td>
                                    </tr>
                                @endforeach
                            </x-ui.table>
                        </x-ui.card>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $scheduledEmails->links() }}
                        </div>
                    @else
                        {{-- Empty State --}}
                        <x-ui.card>
                            <div class="text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-calendar text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {{ tr('No scheduled emails found') }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-6">
                                    {{ tr('Start by sending or scheduling an email') }}
                                </p>
                                <x-ui.primary-button
                                    href="{{ route('saas.emails.send') }}"
                                >
                                    <i class="fas fa-paper-plane"></i>
                                    <span class="ms-2">{{ tr('Send Email') }}</span>
                                </x-ui.primary-button>
                            </div>
                        </x-ui.card>
                    @endif
                </div>
            @endif

            {{-- Templates Tab --}}
            @if($activeTab === 'templates')
                <div class="space-y-4 sm:space-y-6">
                    {{-- Search and Filters --}}
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
                                    model="statusFilterTemplates"
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

                    {{-- Templates List --}}
                    @if($templates && $templates->count() > 0)
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
                                    @if($search || $typeFilter !== 'all' || $statusFilterTemplates !== 'all')
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
            @endif
        </div>
    </x-ui.card>

    {{-- View Email Modal --}}
    @if($viewingEmail)
    <div
        x-data="{ open: true }"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:keydown.escape.window="open = false; $wire.closeViewModal()"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        style="display: block;"
    >
        {{-- Backdrop --}}
        <div
            x-show="open"
            @click="open = false; $wire.closeViewModal()"
            class="absolute inset-0 bg-gradient-to-br from-black/50 via-black/60 to-black/50 backdrop-blur-md"
        ></div>

        {{-- Modal Content --}}
        <div
            x-show="open"
            @click.away="open = false; $wire.closeViewModal()"
            class="relative bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden ring-1 ring-black/5 flex flex-col"
        >
            {{-- Header --}}
            <div class="px-6 pt-5 pb-4 bg-gradient-to-br from-indigo-50 via-purple-50 to-cyan-50 border-b border-indigo-200/50 relative">
                <div class="flex items-center justify-between relative z-10">
                    <div class="min-w-0 flex-1 pr-4">
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ tr('Email Details') }}
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $viewingEmail->template->name }}
                        </p>
                    </div>
                    
                    <button
                        type="button"
                        @click="open = false; $wire.closeViewModal()"
                        class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-white/60 active:scale-95 transition-all duration-200 backdrop-blur-sm"
                    >
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Content (Scrollable) --}}
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-6">
                    {{-- Email Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ tr('Template') }}</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $viewingEmail->template->name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ tr('Subject') }}</label>
                            <p class="text-sm text-gray-900 mt-1">{{ $viewingEmail->template->subject }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ tr('Status') }}</label>
                            <div class="mt-1">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                    ];
                                    $statusColor = $statusColors[$viewingEmail->status] ?? 'info';
                                @endphp
                                <x-ui.badge :type="$statusColor" size="sm">
                                    {{ tr(ucfirst($viewingEmail->status)) }}
                                </x-ui.badge>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ tr('Scheduled At') }}</label>
                            <p class="text-sm text-gray-900 mt-1">
                                @if($viewingEmail->scheduled_at)
                                    {{ $viewingEmail->scheduled_at->format('Y-m-d') }}
                                @else
                                    {{ tr('Immediate') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ tr('Recipient Type') }}</label>
                            <p class="text-sm text-gray-900 mt-1">
                                {{ $viewingEmail->recipient_type === 'single' ? tr('Single Recipient') : tr('Multiple Companies') }}
                            </p>
                        </div>
                        @if($viewingEmail->sent_at)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">{{ tr('Sent At') }}</label>
                            <p class="text-sm text-gray-900 mt-1 sent-time" data-utc-time="{{ $viewingEmail->sent_at->toIso8601String() }}">
                                {{ $viewingEmail->sent_at->format('Y-m-d H:i') }} UTC
                            </p>
                        </div>
                        @endif
                    </div>

                    {{-- Email Body --}}
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">{{ tr('Email Content') }}</label>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 max-h-96 overflow-y-auto">
                            <div class="prose prose-sm max-w-none">
                                {!! $viewingEmail->template->body !!}
                            </div>
                        </div>
                    </div>

                    {{-- Variables Data --}}
                    @if($viewingEmail->variables_data && count($viewingEmail->variables_data) > 0)
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">{{ tr('Template Variables') }}</label>
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <div class="space-y-2">
                                @foreach($viewingEmail->variables_data as $variable => $value)
                                    <div class="flex items-start gap-2">
                                        <span class="text-xs font-semibold text-blue-900 min-w-[120px]">{{ tr(ucfirst(str_replace('_', ' ', $variable))) }}:</span>
                                        <span class="text-sm text-blue-700 flex-1">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Error Message (if failed) --}}
                    @if($viewingEmail->status === 'failed' && $viewingEmail->error_message)
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">{{ tr('Error Message') }}</label>
                        <div class="p-4 bg-red-50 rounded-xl border border-red-200">
                            <p class="text-sm text-red-700">{{ $viewingEmail->error_message }}</p>
                        </div>
                    </div>
                    @endif

                    {{-- Email Logs --}}
                    @if($viewingEmail->logs && $viewingEmail->logs->count() > 0)
                    <div>
                        <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">{{ tr('Email Logs') }}</label>
                        <div class="space-y-2">
                            @foreach($viewingEmail->logs as $log)
                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-gray-900">{{ $log->recipient_email }}</span>
                                        <x-ui.badge :type="$log->status === 'sent' ? 'success' : 'danger'" size="sm">
                                            {{ tr(ucfirst($log->status)) }}
                                        </x-ui.badge>
                                    </div>
                                    @if($log->sent_at)
                                        <p class="text-xs text-gray-500">{{ tr('Sent at') }}: {{ $log->sent_at->format('Y-m-d H:i:s') }}</p>
                                    @endif
                                    @if($log->error_message)
                                        <p class="text-xs text-red-600 mt-1">{{ $log->error_message }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-white flex justify-end">
                <x-ui.secondary-button
                    wire:click="closeViewModal"
                >
                    {{ tr('Close') }}
                </x-ui.secondary-button>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- JavaScript to convert UTC time to device timezone for "Sent At" field only --}}
<script>
(function() {
    function convertSentTime() {
        document.querySelectorAll('.sent-time[data-utc-time]').forEach(function(element) {
            const utcTime = element.getAttribute('data-utc-time');
            if (utcTime && utcTime.trim() !== '') {
                try {
                    const date = new Date(utcTime);
                    
                    if (isNaN(date.getTime())) {
                        console.warn('Invalid date:', utcTime);
                        return;
                    }
                    
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    
                    element.textContent = `${year}-${month}-${day} ${hours}:${minutes}`;
                } catch (e) {
                    console.error('Error converting time:', e, 'UTC time:', utcTime);
                }
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', convertSentTime);
    } else {
        convertSentTime();
    }
    
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', convertSentTime);
        Livewire.hook('morph.added', convertSentTime);
    }
    
    document.addEventListener('livewire:init', function() {
        Livewire.hook('morph.updated', convertSentTime);
    });
})();
</script>
