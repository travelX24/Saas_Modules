<?php

namespace Athka\Saas\Livewire\Emails;

use Athka\Saas\Models\SaasCompany;
use Athka\Saas\Models\ScheduledEmail;
use Livewire\Component;
use Livewire\WithPagination;

class Scheduled extends Component
{
    use WithPagination;

    public string $statusFilter = 'all';

    public string $sendTypeFilter = 'all';

    public ?int $viewingEmailId = null;

    protected $queryString = [
        'statusFilter' => ['except' => 'all'],
        'sendTypeFilter' => ['except' => 'all'],
    ];

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
                session()->flash('error', tr('Only pending emails can be cancelled'));
                return;
            }

            $scheduledEmail->update([
                'status' => 'failed',
                'error_message' => tr('Cancelled by user'),
            ]);

            session()->flash('status', tr('Scheduled email cancelled successfully'));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to cancel scheduled email. Please try again.'));
        }
    }

    public function refresh(): void
    {
        // This method will be called by Livewire polling to refresh the data
        $this->resetPage();
    }

    protected $listeners = ['emailStatusUpdated' => '$refresh'];

    public function viewEmail(int $scheduledEmailId): void
    {
        $this->viewingEmailId = $scheduledEmailId;
    }

    public function closeViewModal(): void
    {
        $this->viewingEmailId = null;
    }

    /**
     * Get system timezone
     */
    private function getSystemTimezone(): string
    {
        // Try to get from environment variable first
        if (env('APP_TIMEZONE')) {
            return env('APP_TIMEZONE');
        }
        
        // For Windows, try to get from system
        if (PHP_OS_FAMILY === 'Windows') {
            // Try to get timezone from Windows registry or system
            $timezone = @shell_exec('tzutil /g 2>nul');
            if ($timezone) {
                $timezone = trim($timezone);
                // Convert Windows timezone to PHP timezone
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
        
        // Fallback: use config or default
        return config('app.timezone', 'Asia/Riyadh');
    }

    public function render()
    {
        $query = ScheduledEmail::with(['template', 'creator'])
            ->when($this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->sendTypeFilter !== 'all', function ($q) {
                $q->where('send_type', $this->sendTypeFilter);
            })
            ->latest();

        $scheduledEmails = $query->paginate(15);

        $recipientItemsByEmail = $this->buildRecipientItemsByEmail($scheduledEmails);

        $viewingEmail = $this->viewingEmailId 
            ? ScheduledEmail::with(['template', 'logs'])->find($this->viewingEmailId)
            : null;

        // Get system timezone for display
        $systemTimezone = $this->getSystemTimezone();

        return view('saas::emails.scheduled', [
            'scheduledEmails' => $scheduledEmails,
            'recipientItemsByEmail' => $recipientItemsByEmail,
            'viewingEmail' => $viewingEmail,
            'systemTimezone' => $systemTimezone,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }

    private function buildRecipientItemsByEmail($scheduledEmails): array
    {
        $companyIds = $scheduledEmails->getCollection()
            ->flatMap(fn ($email) => $email->recipient_company_ids ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($companyIds->isEmpty()) {
            return [];
        }

        $companies = SaasCompany::with([
                'companyAdminUser',
                'adminUser',
            ])
            ->whereIn('id', $companyIds)
            ->get()
            ->keyBy('id');

        $locale = app()->getLocale();
        $itemsByEmail = [];

        foreach ($scheduledEmails as $email) {
            $itemsByEmail[$email->id] = collect($email->recipient_company_ids ?? [])
                ->map(function ($companyId) use ($companies, $locale) {
                    $company = $companies->get((int) $companyId);

                    if (! $company) {
                        return null;
                    }

                    $admin = $company->companyAdminUser ?? $company->adminUser;

                    return [
                        'company_name' => $locale === 'ar'
                            ? ($company->legal_name_ar ?: $company->legal_name_en ?: tr('Unnamed company'))
                            : ($company->legal_name_en ?: $company->legal_name_ar ?: tr('Unnamed company')),
                        'email' => ($admin && $admin->email) ? $admin->email : tr('No admin email found'),
                    ];
                })
                ->filter()
                ->values()
                ->all();
        }

        return $itemsByEmail;
    }
}
