<?php

namespace Athka\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'body',
        'variables',
        'type',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function scheduledEmails()
    {
        return $this->hasMany(ScheduledEmail::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
