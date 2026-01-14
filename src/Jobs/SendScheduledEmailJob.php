<?php

namespace Athka\Saas\Jobs;

use Athka\Saas\Models\EmailLog;
use Athka\Saas\Models\ScheduledEmail;
use Athka\Saas\Models\SaasCompany;
use Athka\Saas\Notifications\TemplateEmailNotification;
use App\Models\User;
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
                    $subject = $this->replaceVariables($template->subject, $variablesData, $recipient, $template);
                    $body = $this->replaceVariables($template->body, $variablesData, $recipient, $template);

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

    private function replaceVariables(string $text, array $variablesData, array $recipient, $template = null): string
    {
        $replacements = $variablesData;

        // Add company-specific variables
        if ($recipient['company']) {
            $company = $recipient['company'];
            
            // Company name
            if (empty($replacements['company_name'])) {
                $replacements['company_name'] = app()->getLocale() === 'ar' 
                    ? $company->legal_name_ar 
                    : ($company->legal_name_en ?: $company->legal_name_ar);
            }
            
            // Admin name - get from company admin (always override to ensure correct admin for each company)
            $admin = $company->users()->whereHas('roles', function ($q) {
                $q->where('name', 'company-admin');
            })->first();
            if (!$admin) {
                $admin = $company->users()->first();
            }
            if ($admin) {
                $replacements['admin_name'] = $admin->name;
            }
            
            // Subscription expiry variables
            if ($company->settings && $company->settings->subscription_ends_at) {
                // Always set expiry_date from company settings, override variablesData
                $replacements['expiry_date'] = $company->settings->subscription_ends_at->format('Y-m-d');
                // Always set days_remaining from company settings, override variablesData
                // Use floor to ensure integer value
                $daysRemaining = now()->diffInDays($company->settings->subscription_ends_at, false);
                $replacements['days_remaining'] = max(0, (int) floor($daysRemaining));
            }
        }

        // Add base variables that should be available in all template types
        // System name from config or app name
        if (empty($replacements['system_name'])) {
            $replacements['system_name'] = config('app.name', 'Athka HR');
        }

        // Support information from config (use from variablesData if provided and not empty, otherwise use default)
        if (empty($replacements['support_email'])) {
            $replacements['support_email'] = config('app.support_email', config('mail.from.address', 'support@athkahr.com'));
        }
        if (empty($replacements['support_phone'])) {
            $replacements['support_phone'] = config('app.support_phone', '+966 12 345 6789');
        }
        if (empty($replacements['support_hours'])) {
            $replacements['support_hours'] = config('app.support_hours', tr('Sunday - Thursday: 9:00 AM - 5:00 PM'));
        }

        // Add user_welcome specific variables
        if ($template && $template->type === 'user_welcome') {
            // Get user information from recipient - prioritize company admin for each company
            // This ensures each company gets its own admin user data when sending to multiple companies
            $user = null;
            if (isset($recipient['user'])) {
                $user = $recipient['user'];
            } elseif ($recipient['company']) {
                // Get admin user from the company - this is the key for multiple companies
                $company = $recipient['company'];
                $admin = $company->users()->whereHas('roles', function ($q) {
                    $q->where('name', 'company-admin');
                })->first();
                if (!$admin) {
                    $admin = $company->users()->first();
                }
                $user = $admin;
            } elseif (isset($recipient['email'])) {
                // Try to find user by email as fallback
                $user = User::where('email', $recipient['email'])->first();
            }

            if ($user) {
                // Always use user data from the specific company for user-specific variables
                // This overrides variablesData to ensure each company gets correct data
                $replacements['user_name'] = $user->name;
                $replacements['username'] = $user->email;
            }

            // Login URL - use company subdomain if available
            if (empty($replacements['login_url'])) {
                if ($recipient['company'] && $recipient['company']->subdomain) {
                    $appUrl = config('app.url', url('/'));
                    $loginUrl = str_replace('://', '://' . $recipient['company']->subdomain . '.', $appUrl) . '/login';
                    $replacements['login_url'] = $loginUrl;
                } else {
                    $replacements['login_url'] = url('/login');
                }
            }

            // Reset password URL - use company subdomain if available
            if (empty($replacements['reset_password_url'])) {
                if ($recipient['company'] && $recipient['company']->subdomain) {
                    $appUrl = config('app.url', url('/'));
                    $resetUrl = str_replace('://', '://' . $recipient['company']->subdomain . '.', $appUrl) . '/password/reset';
                    $replacements['reset_password_url'] = $resetUrl;
                } else {
                    $replacements['reset_password_url'] = url('/password/reset');
                }
            }
        }

        // Add subscription_expiry specific variables
        if ($template && $template->type === 'subscription_expiry') {
            // Ensure admin_name is set (should already be set from company-specific section, but double-check)
            if (empty($replacements['admin_name']) && $recipient['company']) {
                $company = $recipient['company'];
                $admin = $company->users()->whereHas('roles', function ($q) {
                    $q->where('name', 'company-admin');
                })->first();
                if (!$admin) {
                    $admin = $company->users()->first();
                }
                if ($admin) {
                    $replacements['admin_name'] = $admin->name;
                }
            }
        }

        // Replace all variables - support both {variable} and {{variable}} formats
        foreach ($replacements as $key => $value) {
            // Convert null to empty string, but don't skip - we want to replace even empty values
            // to avoid showing {variable} in the email
            $replacementValue = $value ?? '';
            
            // Replace single curly braces: {variable}
            $text = str_replace('{'.$key.'}', $replacementValue, $text);
            // Replace double curly braces: {{variable}}
            $text = str_replace('{{'.$key.'}}', $replacementValue, $text);
        }

        return $text;
    }
}
