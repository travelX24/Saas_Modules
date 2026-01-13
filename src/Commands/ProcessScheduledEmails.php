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

        // Get all pending emails for debugging
        $allPending = ScheduledEmail::where('status', 'pending')->get();
        \Log::info('ProcessScheduledEmails: All pending emails', [
            'count' => $allPending->count(),
            'emails' => $allPending->map(function ($email) use ($nowUtc, $nowSystem, $systemTimezone) {
                $scheduledAt = $email->scheduled_at;
                if ($scheduledAt) {
                    // scheduled_at is stored in UTC, convert to system timezone for comparison
                    $scheduledAtSystem = $scheduledAt->copy()->setTimezone($systemTimezone);
                    // Compare in UTC (both are in UTC)
                    $isDue = $scheduledAt->lte($nowUtc);
                } else {
                    $scheduledAtSystem = null;
                    $isDue = false;
                }
                
                return [
                    'id' => $email->id,
                    'scheduled_at_utc' => $scheduledAt ? $scheduledAt->toDateTimeString() : null,
                    'scheduled_at_system' => $scheduledAtSystem ? $scheduledAtSystem->toDateTimeString() : null,
                    'scheduled_at_timezone' => $scheduledAt ? $scheduledAt->timezone->getName() : null,
                    'is_due' => $isDue,
                    'current_time_utc' => $nowUtc->toDateTimeString(),
                    'current_time_system' => $nowSystem->toDateTimeString(),
                ];
            })->toArray(),
        ]);

        // Compare in UTC (database stores in UTC) but convert both to same timezone
        // Since scheduled_at is stored in UTC in database, we compare UTC to UTC
        $nowUtc = now('UTC');
        $scheduledEmails = ScheduledEmail::where('status', 'pending')
            ->where(function($query) use ($nowUtc) {
                $query->where('scheduled_at', '<=', $nowUtc)
                      ->orWhereNull('scheduled_at'); // Handle immediate emails
            })
            ->get();

        \Log::info('ProcessScheduledEmails: Emails due to be sent', [
            'count' => $scheduledEmails->count(),
            'current_time_utc' => $nowUtc->toDateTimeString(),
            'current_time_system' => $nowSystem->toDateTimeString(),
            'query_condition' => 'scheduled_at <= ' . $nowUtc->toDateTimeString() . ' (UTC)',
        ]);

        if ($scheduledEmails->isEmpty()) {
            $this->info('No scheduled emails to process.');
            \Log::info('ProcessScheduledEmails: No scheduled emails to process', [
                'total_pending' => $allPending->count(),
                'current_time_utc' => $nowUtc->toDateTimeString(),
                'current_time_system' => $nowSystem->toDateTimeString(),
            ]);
            return self::SUCCESS;
        }

        $this->info("Found {$scheduledEmails->count()} scheduled email(s) to process.");
        \Log::info("ProcessScheduledEmails: Found {$scheduledEmails->count()} scheduled email(s) to process", [
            'emails' => $scheduledEmails->pluck('id')->toArray(),
        ]);

        foreach ($scheduledEmails as $scheduledEmail) {
            try {
                $this->info("Processing scheduled email ID: {$scheduledEmail->id} (scheduled for: {$scheduledEmail->scheduled_at})");
                
                // Update status to processing
                $scheduledEmail->update(['status' => 'processing']);
                
                \Log::info("ProcessScheduledEmails: Processing email ID {$scheduledEmail->id}", [
                    'scheduled_at' => $scheduledEmail->scheduled_at,
                    'template_id' => $scheduledEmail->template_id,
                ]);
                
                // Try to send synchronously first (for immediate processing)
                try {
                    SendScheduledEmailJob::dispatchSync($scheduledEmail);
                    $this->info("✓ Successfully processed scheduled email ID: {$scheduledEmail->id}");
                    \Log::info("ProcessScheduledEmails: Successfully processed email ID {$scheduledEmail->id}");
                } catch (\Throwable $jobException) {
                    // If dispatchSync fails, try async dispatch
                    \Log::warning("ProcessScheduledEmails: dispatchSync failed, trying async dispatch", [
                        'email_id' => $scheduledEmail->id,
                        'error' => $jobException->getMessage(),
                    ]);
                    
                    SendScheduledEmailJob::dispatch($scheduledEmail);
                    $this->info("Dispatched job asynchronously for scheduled email ID: {$scheduledEmail->id}");
                    \Log::info("ProcessScheduledEmails: Dispatched job asynchronously for email ID {$scheduledEmail->id}");
                }
            } catch (\Throwable $e) {
                $this->error("✗ Failed to process scheduled email ID: {$scheduledEmail->id}");
                $this->error("Error: {$e->getMessage()}");
                
                \Log::error("ProcessScheduledEmails: Failed to process email", [
                    'email_id' => $scheduledEmail->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Mark as failed
                $scheduledEmail->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
            }
        }

        $this->info('Done processing scheduled emails.');
        \Log::info('ProcessScheduledEmails: Finished processing scheduled emails');
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
