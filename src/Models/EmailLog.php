<?php

namespace Athka\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'scheduled_email_id',
        'recipient_email',
        'subject',
        'body',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function scheduledEmail()
    {
        return $this->belongsTo(ScheduledEmail::class, 'scheduled_email_id');
    }
}
