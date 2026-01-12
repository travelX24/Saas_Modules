<?php

namespace Athka\Saas\Traits;

use App\Models\User;
use Athka\Saas\Models\SaasCompanyOtherinfo;

trait ValidatesUserLimit
{
    /**
     * ✅ التحقق من إمكانية إضافة مستخدم جديد للشركة
     */
    protected function validateUserLimit(?int $companyId): void
    {
        if (! $companyId) {
            $this->addError('saas_company_id', tr('Company ID is required'));

            return;
        }

        $settings = SaasCompanyOtherinfo::where('company_id', $companyId)->first();

        if (! $settings) {
            $this->addError('saas_company_id', tr('Company settings not found'));

            return;
        }

        $currentUsersCount = User::where('saas_company_id', $companyId)->count();
        $allowedUsers = $settings->allowed_users ?? 0;

        if ($currentUsersCount >= $allowedUsers) {
            $this->addError(
                'saas_company_id',
                tr('Maximum allowed users limit reached. Current limit is :limit users.', ['limit' => $allowedUsers])
            );
        }
    }
}



