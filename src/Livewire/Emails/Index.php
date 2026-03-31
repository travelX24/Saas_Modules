<?php

namespace Athka\Saas\Livewire\Emails;

use Athka\Saas\Jobs\SendScheduledEmailJob;
use Athka\Saas\Models\EmailTemplate;
use Athka\Saas\Models\ScheduledEmail;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'emails'; // 'emails' or 'templates'

    // For emails tab
    public string $statusFilter = 'all';
    public string $sendTypeFilter = 'all';
    public ?int $viewingEmailId = null;

    // For templates tab
    public string $search = '';
    public string $typeFilter = 'all';
    public string $statusFilterTemplates = 'all';

    protected $queryString = [
        'activeTab' => ['except' => 'emails', 'as' => 'tab'],
        'statusFilter' => ['except' => 'all'],
        'sendTypeFilter' => ['except' => 'all'],
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'statusFilterTemplates' => ['except' => 'all'],
    ];

    public function mount(?string $tab = null): void
    {
        if ($tab && in_array($tab, ['emails', 'templates'])) {
            $this->activeTab = $tab;
        } elseif (request()->has('tab')) {
            $tabFromRequest = request()->query('tab');
            if ($tabFromRequest && in_array($tabFromRequest, ['emails', 'templates'])) {
                $this->activeTab = $tabFromRequest;
            } else {
                $this->activeTab = 'emails';
            }
        } else {
            $this->activeTab = 'emails';
        }
    }

    public function setActiveTab(string $tab): void
    {
        if (in_array($tab, ['emails', 'templates'])) {
            $this->activeTab = $tab;
            $this->resetPage();

            if ($tab === 'emails') {
                $this->statusFilter = 'all';
                $this->sendTypeFilter = 'all';
            } else {
                $this->search = '';
                $this->typeFilter = 'all';
                $this->statusFilterTemplates = 'all';
            }
        }
    }

    public function clearAllFilters(): void
    {
        if ($this->activeTab === 'emails') {
            $this->statusFilter = 'all';
            $this->sendTypeFilter = 'all';
        } else {
            $this->search = '';
            $this->typeFilter = 'all';
            $this->statusFilterTemplates = 'all';
        }
        $this->resetPage();
    }

    private function toastSuccess(string $message): void
    {
        $this->dispatch(
            'toast',
            type: 'success',
            title: tr('Success'),
            message: $message
        );
    }

    private function toastError(string $message): void
    {
        $this->dispatch(
            'toast',
            type: 'error',
            title: $message,
            message: ''
        );
    }

    // Emails tab methods
    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSendTypeFilter(): void
    {
        $this->resetPage();
    }

    public function cancelScheduled(int $scheduledEmailId): void
    {
        try {
            $scheduledEmail = ScheduledEmail::findOrFail($scheduledEmailId);

            if ($scheduledEmail->status !== 'pending') {
                $this->toastError(tr('Only pending emails can be cancelled'));
                return;
            }

            $scheduledEmail->update([
                'status' => 'failed',
                'error_message' => tr('Cancelled by user'),
            ]);

            $this->toastSuccess(tr('Scheduled email cancelled successfully'));
        } catch (\Throwable $e) {
            report($e);
            $this->toastError(tr('Failed to cancel scheduled email. Please try again.'));
        }
    }

    public function resendEmail(int $scheduledEmailId): void
    {
        try {
            $scheduledEmail = ScheduledEmail::findOrFail($scheduledEmailId);

            $newScheduledEmail = $scheduledEmail->replicate();

            $newScheduledEmail->status = 'pending';
            $newScheduledEmail->error_message = null;
            $newScheduledEmail->sent_at = null;
            $newScheduledEmail->failed_at = null;
            $newScheduledEmail->send_type = 'immediate';
            $newScheduledEmail->scheduled_at = now();

            $newScheduledEmail->save();

            SendScheduledEmailJob::dispatchSync($newScheduledEmail);

            $this->toastSuccess(tr('Email resent successfully'));
        } catch (\Throwable $e) {
            report($e);
            $this->toastError(tr('Failed to resend email. Please try again.'));
        }
    }

    public function viewEmail(int $scheduledEmailId): void
    {
        $this->viewingEmailId = $scheduledEmailId;
    }

    public function closeViewModal(): void
    {
        $this->viewingEmailId = null;
    }

    // Templates tab methods
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilterTemplates(): void
    {
        $this->resetPage();
    }

    public function deleteTemplate(int $templateId): void
    {
        try {
            $template = EmailTemplate::findOrFail($templateId);
            $template->delete();

            $this->toastSuccess(tr('Template deleted successfully'));
        } catch (\Throwable $e) {
            report($e);
            $this->toastError(tr('Failed to delete template. Please try again.'));
        }
    }

    public function toggleStatus(int $templateId): void
    {
        try {
            $template = EmailTemplate::findOrFail($templateId);
            $template->is_active = !$template->is_active;
            $template->save();

            $status = $template->is_active
                ? tr('Template activated successfully')
                : tr('Template deactivated successfully');

            $this->toastSuccess($status);
        } catch (\Throwable $e) {
            report($e);
            $this->toastError(tr('Failed to update template status. Please try again.'));
        }
    }

    /**
     * Get system timezone
     */
    private function getSystemTimezone(): string
    {
        if (env('APP_TIMEZONE')) {
            return env('APP_TIMEZONE');
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $timezone = @shell_exec('tzutil /g 2>nul');
            if ($timezone) {
                $timezone = trim($timezone);
                $windowsToPhp = [
                    'Arab Standard Time' => 'Asia/Riyadh',
                    'Arabian Standard Time' => 'Asia/Dubai',
                    'Egypt Standard Time' => 'Africa/Cairo',
                    'Turkey Standard Time' => 'Europe/Istanbul',
                    'GMT Standard Time' => 'Europe/London',
                    'Central European Standard Time' => 'Europe/Berlin',
                ];

                return $windowsToPhp[$timezone] ?? 'Asia/Riyadh';
            }
        }

        return config('app.timezone', 'Asia/Riyadh');
    }

    public function render()
    {
        $scheduledEmails = null;
        $viewingEmail = null;
        $systemTimezone = null;

        if ($this->activeTab === 'emails') {
            $query = ScheduledEmail::with(['template', 'creator'])
                ->when($this->statusFilter !== 'all', function ($q) {
                    $q->where('status', $this->statusFilter);
                })
                ->when($this->sendTypeFilter !== 'all', function ($q) {
                    $q->where('send_type', $this->sendTypeFilter);
                })
                ->latest();

            $scheduledEmails = $query->paginate(15);

            $viewingEmail = $this->viewingEmailId
                ? ScheduledEmail::with(['template', 'logs'])->find($this->viewingEmailId)
                : null;

            $systemTimezone = $this->getSystemTimezone();
        }

        $templates = null;

        $types = [
            ['value' => 'all', 'label' => tr('All Types')],
            ['value' => 'subscription_expiry', 'label' => tr('Subscription Expiry')],
            ['value' => 'subscription_anniversary', 'label' => tr('Subscription Anniversary')],
            ['value' => 'update_notification', 'label' => tr('Update Notification')],
            ['value' => 'greeting', 'label' => tr('Greeting')],
            ['value' => 'user_welcome', 'label' => tr('User Welcome')],
            ['value' => 'new_year_greeting', 'label' => tr('New Year Greeting')],
            ['value' => 'holiday_greeting', 'label' => tr('Holiday Greeting')],
            ['value' => 'custom', 'label' => tr('Custom')],
        ];

        if ($this->activeTab === 'templates') {
            $query = EmailTemplate::query()
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('subject', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->typeFilter !== 'all', function ($q) {
                    $q->where('type', $this->typeFilter);
                })
                ->when($this->statusFilterTemplates !== 'all', function ($q) {
                    if ($this->statusFilterTemplates === 'active') {
                        $q->where('is_active', true);
                    } else {
                        $q->where('is_active', false);
                    }
                })
                ->latest();

            $templates = $query->paginate(12);
        }

        return view('saas::emails.index', [
            'scheduledEmails' => $scheduledEmails,
            'viewingEmail' => $viewingEmail,
            'systemTimezone' => $systemTimezone,
            'templates' => $templates,
            'types' => $types,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}