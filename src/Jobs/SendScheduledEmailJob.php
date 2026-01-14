<?php

namespace Athka\Saas\Jobs;

use Athka\Saas\Models\EmailLog;
use Athka\Saas\Models\ScheduledEmail;
use Athka\Saas\Models\SaasCompany;
use Athka\Saas\Notifications\TemplateEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendScheduledEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ScheduledEmail $scheduledEmail
    ) {}

    public function handle(): void
    {
        try {
            $this->scheduledEmail->update(['status' => 'processing']);

            $template = $this->scheduledEmail->template;
            $variablesData = $this->scheduledEmail->variables_data ?? [];

            // Get recipients
            $recipients = $this->getRecipients();

            if (empty($recipients)) {
                throw new \Exception(tr('No recipients found'));
            }

            // Send to each recipient
            foreach ($recipients as $recipient) {
                try {
                    // Replace variables in subject and body
                    $subject = $this->replaceVariables($template->subject, $variablesData, $recipient);
                    $body = $this->replaceVariables($template->body, $variablesData, $recipient);

                    // Get company name for greeting
                    $companyName = null;
                    $recipientName = null;
                    if ($recipient['company']) {
                        $company = $recipient['company'];
                        $companyName = app()->getLocale() === 'ar' 
                            ? $company->legal_name_ar 
                            : ($company->legal_name_en ?: $company->legal_name_ar);
                        
                        // Get admin name if available
                        $admin = $company->users()->whereHas('roles', function ($q) {
                            $q->where('name', 'company-admin');
                        })->first();
                        if (!$admin) {
                            $admin = $company->users()->first();
                        }
                        if ($admin) {
                            $recipientName = $admin->name;
                        }
                    }

                    \Log::info("SendScheduledEmailJob: Attempting to send email", [
                        'recipient_email' => $recipient['email'],
                        'subject' => $subject,
                        'company_name' => $companyName,
                    ]);

                    // Send email with company and recipient info
                    Mail::to($recipient['email'])->send(
                        new TemplateEmailNotification($subject, $body, $companyName, $recipientName, $recipient['email'])
                    );

                    \Log::info("SendScheduledEmailJob: Email sent successfully", [
                        'recipient_email' => $recipient['email'],
                    ]);

                    // Log success
                    EmailLog::create([
                        'scheduled_email_id' => $this->scheduledEmail->id,
                        'recipient_email' => $recipient['email'],
                        'subject' => $subject,
                        'body' => $body,
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    \Log::error("SendScheduledEmailJob: Failed to send email", [
                        'recipient_email' => $recipient['email'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    // Log failure
                    EmailLog::create([
                        'scheduled_email_id' => $this->scheduledEmail->id,
                        'recipient_email' => $recipient['email'],
                        'subject' => $template->subject,
                        'body' => $template->body,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }

            // Update scheduled email status
            $this->scheduledEmail->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->scheduledEmail->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function getRecipients(): array
    {
        $recipients = [];

        if ($this->scheduledEmail->recipient_type === 'single') {
            // Single recipient: get company admin email from selected company
            if ($this->scheduledEmail->recipient_company_ids && count($this->scheduledEmail->recipient_company_ids) > 0) {
                $companyId = $this->scheduledEmail->recipient_company_ids[0];
                $company = SaasCompany::with('users')->find($companyId);
                
                if (!$company) {
                    \Log::error("SendScheduledEmailJob: Company not found with ID: {$companyId}");
                    return $recipients;
                }

                // Get company admin email - try role first, then fallback to first user
                $admin = $company->users()->whereHas('roles', function ($q) {
                    $q->where('name', 'company-admin');
                })->first();

                // Fallback: if no admin with role, get first user of the company
                if (!$admin) {
                    $admin = $company->users()->first();
                }

                if ($admin && $admin->email) {
                    \Log::info("SendScheduledEmailJob: Found recipient", [
                        'company_id' => $companyId,
                        'company_name' => $company->legal_name_ar,
                        'admin_email' => $admin->email,
                        'admin_name' => $admin->name,
                    ]);
                    
                    $recipients[] = [
                        'email' => $admin->email,
                        'company' => $company,
                    ];
                } else {
                    \Log::error("SendScheduledEmailJob: No admin user found for company", [
                        'company_id' => $companyId,
                        'company_name' => $company->legal_name_ar,
                        'users_count' => $company->users()->count(),
                    ]);
                }
            }
        } else {
            // Multiple companies
            $companyIds = $this->scheduledEmail->recipient_company_ids ?? [];
            $companies = SaasCompany::with('users')->whereIn('id', $companyIds)->get();

            foreach ($companies as $company) {
                // Get company admin email - try role first, then fallback to first user
                $admin = $company->users()->whereHas('roles', function ($q) {
                    $q->where('name', 'company-admin');
                })->first();

                // Fallback: if no admin with role, get first user of the company
                if (!$admin) {
                    $admin = $company->users()->first();
                }

                if ($admin && $admin->email) {
                    \Log::info("SendScheduledEmailJob: Found recipient", [
                        'company_id' => $company->id,
                        'company_name' => $company->legal_name_ar,
                        'admin_email' => $admin->email,
                        'admin_name' => $admin->name,
                    ]);
                    
                    $recipients[] = [
                        'email' => $admin->email,
                        'company' => $company,
                    ];
                } else {
                    \Log::error("SendScheduledEmailJob: No admin user found for company", [
                        'company_id' => $company->id,
                        'company_name' => $company->legal_name_ar,
                        'users_count' => $company->users()->count(),
                    ]);
                }
            }
        }

        \Log::info("SendScheduledEmailJob: Total recipients found", [
            'count' => count($recipients),
            'recipients' => array_map(fn($r) => $r['email'], $recipients),
        ]);

        return $recipients;
    }

    private function replaceVariables(string $text, array $variablesData, array $recipient): string
    {
        $replacements = $variablesData;

        // Add company-specific variables
        if ($recipient['company']) {
            $company = $recipient['company'];
            $replacements['company_name'] = $replacements['company_name'] ?? 
                (app()->getLocale() === 'ar' ? $company->legal_name_ar : ($company->legal_name_en ?: $company->legal_name_ar));
            
            if ($company->settings && $company->settings->subscription_ends_at) {
                $replacements['expiry_date'] = $replacements['expiry_date'] ?? 
                    $company->settings->subscription_ends_at->format('Y-m-d');
                $replacements['days_remaining'] = $replacements['days_remaining'] ?? 
                    max(0, now()->diffInDays($company->settings->subscription_ends_at, false));
            }
        }

        // Replace all variables - support both {variable} and {{variable}} formats
        foreach ($replacements as $key => $value) {
            // Replace single curly braces: {variable}
            $text = str_replace('{'.$key.'}', $value, $text);
            // Replace double curly braces: {{variable}}
            $text = str_replace('{{'.$key.'}}', $value, $text);
        }

        return $text;
    }
}
