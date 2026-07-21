<?php

namespace Athka\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class SaasCompanyOtherinfo extends Model
{
    protected $table = 'saas_company_otherinfo';

    protected $fillable = [
        'company_id',
        'license_number', 'tax_number', 'cr_number',
        'subscription_starts_at', 'subscription_ends_at',
        'allowed_users', 'timezone', 'default_locale', 'datetime_format',
        'default_annual_leave_days', 'excluded_leave_contract_types',
        'general_manager_employee_id',
    ];

    protected $casts = [
        'subscription_starts_at' => 'date',
        'subscription_ends_at' => 'date',
        'allowed_users' => 'integer',
        'general_manager_employee_id' => 'integer',
        'excluded_leave_contract_types' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(SaasCompany::class, 'company_id');
    }
}
