<?php

namespace Athka\Saas\Livewire\Emails;

use Athka\Saas\Models\EmailTemplate;
use Athka\Saas\Models\SaasCompany;
use Athka\Saas\Models\ScheduledEmail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Send extends Component
{
    public ?int $templateId = null;

    public string $sendType = 'immediate'; // immediate or scheduled

    public string $recipientType = 'single'; // single or multiple

    public ?int $recipientCompanyId = null; // For single recipient

    public array $selectedCompanyIds = [];

    public ?string $scheduledAt = null;

    public array $variablesData = [];

    public function mount(?int $templateId = null): void
    {
        if ($templateId) {
            $this->templateId = $templateId;
            $this->loadTemplateVariables();
        }
    }

    public function updatedTemplateId($value): void
    {
        if ($value) {
            $this->loadTemplateVariables();
            // Auto-fill if company already selected
            if ($this->recipientType === 'single' && $this->recipientCompanyId) {
                $this->autoFillVariablesFromCompany($this->recipientCompanyId);
            }
        } else {
            $this->variablesData = [];
        }
    }

    public function updatedRecipientCompanyId($value): void
    {
        if ($value && $this->recipientType === 'single') {
            $this->autoFillVariablesFromCompany($value);
        }
    }

    public function updatedRecipientType($value): void
    {
        if ($value === 'single' && $this->recipientCompanyId) {
            $this->autoFillVariablesFromCompany($this->recipientCompanyId);
        } elseif ($value === 'multiple') {
            // Clear auto-filled data when switching to multiple
            $this->loadTemplateVariables();
        }
    }

    private function loadTemplateVariables(): void
    {
        if (!$this->templateId) {
            $this->variablesData = [];
            return;
        }

        $template = EmailTemplate::find($this->templateId);
        if (!$template) {
            $this->variablesData = [];
            return;
        }

        // Initialize variables with empty values
        $variables = $template->variables ?? [];
        foreach ($variables as $variable) {
            if (!isset($this->variablesData[$variable])) {
                $this->variablesData[$variable] = '';
            }
        }
    }

    private function autoFillVariablesFromCompany(int $companyId): void
    {
        if (!$this->templateId) {
            return;
        }

        $company = SaasCompany::with('settings')->find($companyId);
        if (!$company) {
            return;
        }

        // Load template variables first
        $this->loadTemplateVariables();

        // Auto-fill from company data (only if not already filled)
        foreach ($this->variablesData as $variable => $value) {
            if (empty($value)) {
                switch ($variable) {
                    case 'company_name':
                        $this->variablesData[$variable] = app()->getLocale() === 'ar' 
                            ? $company->legal_name_ar 
                            : ($company->legal_name_en ?: $company->legal_name_ar);
                        break;
                    
                    case 'admin_name':
                        $admin = $company->users()->whereHas('roles', function ($q) {
                            $q->where('name', 'company-admin');
                        })->first();
                        // Fallback: if no admin with role, get first user of the company
                        if (!$admin) {
                            $admin = $company->users()->first();
                        }
                        if ($admin) {
                            $this->variablesData[$variable] = $admin->name;
                        }
                        break;
                    
                    case 'expiry_date':
                        if ($company->settings && $company->settings->subscription_ends_at) {
                            $this->variablesData[$variable] = $company->settings->subscription_ends_at->format('Y-m-d');
                        }
                        break;
                    
                    case 'days_remaining':
                        if ($company->settings && $company->settings->subscription_ends_at) {
                            $this->variablesData[$variable] = max(0, now()->diffInDays($company->settings->subscription_ends_at, false));
                        }
                        break;
                }
            }
        }
    }

    public function send(): void
    {
        try {
            // Build validation rules based on recipient type
            $rules = [
                'templateId' => 'required|exists:email_templates,id',
                'sendType' => 'required|in:immediate,scheduled',
                'recipientType' => 'required|in:single,multiple',
                'scheduledAt' => 'required_if:sendType,scheduled|nullable|date|after:now',
            ];

            // Add conditional validation based on recipient type
            if ($this->recipientType === 'single') {
                $rules['recipientCompanyId'] = 'required|exists:saas_companies,id';
            } else {
                $rules['selectedCompanyIds'] = 'required|array|min:1';
                $rules['selectedCompanyIds.*'] = 'exists:saas_companies,id';
            }

            $this->validate($rules, [
                'templateId.required' => tr('Please select a template'),
                'recipientCompanyId.required' => tr('Please select a company'),
                'recipientCompanyId.exists' => tr('Selected company does not exist'),
                'selectedCompanyIds.required' => tr('Please select at least one company'),
                'selectedCompanyIds.min' => tr('Please select at least one company'),
                'scheduledAt.required_if' => tr('Please select scheduled date and time'),
                'scheduledAt.after' => tr('Scheduled date must be in the future'),
            ]);

            // Convert scheduledAt string to Carbon instance if it's a scheduled email
            // Use system timezone (same as device timezone)
            if ($this->sendType === 'scheduled' && $this->scheduledAt) {
                // Get system timezone - try to detect Windows timezone
                $systemTimezone = $this->getSystemTimezone();
                
                // Parse the datetime string in system timezone, then convert to UTC for storage
                $scheduledAt = \Carbon\Carbon::parse($this->scheduledAt, $systemTimezone)
                    ->setTimezone('UTC'); // Convert to UTC for database storage
            } else {
                $scheduledAt = now('UTC');
            }

            $scheduledEmail = ScheduledEmail::create([
                'template_id' => $this->templateId,
                'send_type' => $this->sendType,
                'recipient_type' => $this->recipientType,
                'recipient_email' => null, // No longer used for single recipient
                'recipient_company_ids' => $this->recipientType === 'single' 
                    ? [$this->recipientCompanyId] 
                    : $this->selectedCompanyIds,
                'scheduled_at' => $scheduledAt,
                'status' => 'pending',
                'variables_data' => $this->variablesData,
                'created_by' => Auth::id(),
            ]);

            // If immediate, dispatch job to send
            if ($this->sendType === 'immediate') {
                // Try to send synchronously first (for testing/debugging)
                // If queue is not running, this will still work
                try {
                    \Athka\Saas\Jobs\SendScheduledEmailJob::dispatchSync($scheduledEmail);
                } catch (\Throwable $e) {
                    // Fallback to async dispatch if sync fails
                    \Log::warning("Failed to send email synchronously, falling back to queue", [
                        'error' => $e->getMessage(),
                    ]);
                    \Athka\Saas\Jobs\SendScheduledEmailJob::dispatch($scheduledEmail);
                }
            }

            $message = $this->sendType === 'immediate' 
                ? tr('Email sent successfully') 
                : tr('Email scheduled successfully');
            
            // Store success message in session for toast notification
            session()->flash('status', $message);
            
            // Redirect to emails page with emails tab
            $this->redirect(route('saas.emails.index', ['tab' => 'emails']), navigate: false);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show them in the form
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            
            // Store error message in session for toast notification
            session()->flash('error', tr('Failed to send email. Please try again.'));
        }
    }

    /**
     * Get system timezone (Windows or Unix)
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
            $timezone = shell_exec('tzutil /g 2>nul');
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
                return $windowsToPhp[$timezone] ?? 'UTC';
            }
        }
        
        // Fallback: try to detect from date_default_timezone_get or use UTC
        $phpTimezone = date_default_timezone_get();
        return $phpTimezone !== 'UTC' ? $phpTimezone : 'Asia/Riyadh'; // Default to Riyadh if UTC
    }

    public function render()
    {
        $templates = EmailTemplate::where('is_active', true)
            ->orderBy('name')
            ->get();

        $companies = SaasCompany::where('is_active', true)
            ->orderBy('legal_name_ar')
            ->get();

        return view('saas::emails.send', [
            'templates' => $templates,
            'companies' => $companies,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
