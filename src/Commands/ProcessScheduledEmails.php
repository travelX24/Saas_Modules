<?php

namespace Athka\Saas\Commands;

use Athka\Saas\Jobs\SendScheduledEmailJob;
use Athka\Saas\Models\ScheduledEmail;
use Illuminate\Console\Command;

class ProcessScheduledEmails extends Command
{
    protected $signature = 'saas:process-scheduled-emails';

    protected $description = 'Process scheduled emails that are due to be sent';

    public function handle(): int
    {
        $this->info('Processing scheduled emails...');
        
        // Use system timezone (same as device timezone) for comparison
        $systemTimezone = $this->getSystemTimezone();
        $nowUtc = now('UTC');
        $nowSystem = now($systemTimezone);
        
        \Log::info('ProcessScheduledEmails: Starting to process scheduled emails', [
            'current_time_utc' => $nowUtc->toDateTimeString(),
            'current_time_system' => $nowSystem->toDateTimeString(),
            'system_timezone' => $systemTimezone,
            'app_timezone' => config('app.timezone', 'UTC'),
            'php_timezone' => date_default_timezone_get(),
        ]);

        // 1. Handle stuck emails (in 'processing' status for more than 15 minutes)
        // This ensures that if a process died, the emails will be retried
        $stuckEmails = ScheduledEmail::where('status', 'processing')
            ->where('updated_at', '<=', now()->subMinutes(15))
            ->get();

        if ($stuckEmails->isNotEmpty()) {
            \Log::warning("ProcessScheduledEmails: Resetting stuck processing emails", [
                'count' => $stuckEmails->count(),
                'ids' => $stuckEmails->pluck('id')->toArray(),
            ]);
            foreach ($stuckEmails as $stuckEmail) {
                $stuckEmail->update(['status' => 'pending']);
            }
            $this->info("Reset {$stuckEmails->count()} stuck emails to pending.");
        }

        // Compare using app timezone since scheduled_at is stored in local time
        $now = now(config('app.timezone', 'Asia/Riyadh'));
        $scheduledEmails = ScheduledEmail::where('status', 'pending')
            ->where(function($query) use ($now) {
                $query->where('scheduled_at', '<=', $now)
                      ->orWhereNull('scheduled_at'); // Handle immediate emails
            })
            ->get();

        \Log::info('ProcessScheduledEmails: Emails due to be sent', [
            'count' => $scheduledEmails->count(),
            'current_time_utc' => $nowUtc->toDateTimeString(),
            'current_time_system' => $nowSystem->toDateTimeString(),
            'query_condition' => 'scheduled_at <= ' . $now->toDateTimeString() . ' (' . config('app.timezone', 'Asia/Riyadh') . ')',
        ]);

        if ($scheduledEmails->isEmpty()) {
            $this->info('No scheduled emails to process.');
            return self::SUCCESS;
        }

        $this->info("Found {$scheduledEmails->count()} scheduled email(s) to process.");

        foreach ($scheduledEmails as $scheduledEmail) {
            /** @var \Athka\Saas\Models\ScheduledEmail $scheduledEmail */
            try {
                $this->info("Processing scheduled email ID: {$scheduledEmail->id}");
                
                // Update status to processing
                $scheduledEmail->update(['status' => 'processing']);
                
                // Always use async dispatch for multiple recipients to avoid timeouts
                // Use sync only for single recipient if preferred, but async is safer
                if ($scheduledEmail->recipient_type === 'multiple' || count($scheduledEmail->recipient_company_ids ?? []) > 1) {
                    SendScheduledEmailJob::dispatch($scheduledEmail);
                    $this->info("Dispatched job asynchronously for scheduled email ID: {$scheduledEmail->id}");
                } else {
                    // For single recipient, try sync first but fallback to async
                    try {
                        SendScheduledEmailJob::dispatchSync($scheduledEmail);
                        $this->info("✓ Successfully processed scheduled email ID: {$scheduledEmail->id}");
                    } catch (\Throwable $jobException) {
                        SendScheduledEmailJob::dispatch($scheduledEmail);
                        $this->info("Dispatched job asynchronously after sync failed for ID: {$scheduledEmail->id}");
                    }
                }
            } catch (\Throwable $e) {
                $this->error("✗ Failed to process scheduled email ID: {$scheduledEmail->id}");
                \Log::error("ProcessScheduledEmails: Failed to process email", [
                    'email_id' => $scheduledEmail->id,
                    'error' => $e->getMessage(),
                ]);
                
                $scheduledEmail->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
            }
        }

        $this->info('Done processing scheduled emails.');
        return self::SUCCESS;
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
                return $windowsToPhp[$timezone] ?? 'UTC';
            }
        }
        
        // Fallback: try to detect from date_default_timezone_get or use UTC
        $phpTimezone = date_default_timezone_get();
        return $phpTimezone !== 'UTC' ? $phpTimezone : 'Asia/Riyadh'; // Default to Riyadh if UTC
    }
}
