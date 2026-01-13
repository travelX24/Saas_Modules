<?php

namespace Athka\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledEmail extends Model
{
    protected $fillable = [
        'template_id',
        'send_type',
        'recipient_type',
        'recipient_company_ids',
        'recipient_email',
        'scheduled_at',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'variables_data',
        'created_by',
    ];

    protected $casts = [
        'recipient_company_ids' => 'array',
        'variables_data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'scheduled_email_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function recipientCompanies()
    {
        if (!$this->recipient_company_ids || !is_array($this->recipient_company_ids)) {
            return collect();
        }

        return SaasCompany::whereIn('id', $this->recipient_company_ids)->get();
    }
}
