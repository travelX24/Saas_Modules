<?php

namespace Athka\Saas\Models;

use Athka\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'saas_company_id',
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'saas_company_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(SaasCompany::class, 'saas_company_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }

    public function users(): HasMany
    {
        $userModel = config('auth.providers.users.model');

        return $this->hasMany($userModel, 'branch_id');
    }

    public function allowedUsers(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model');

        return $this->belongsToMany($userModel, 'branch_user_access', 'branch_id', 'user_id')
            ->withTimestamps();
    }
}
