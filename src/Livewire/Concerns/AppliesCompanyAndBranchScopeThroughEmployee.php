<?php

namespace Athka\Saas\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait AppliesCompanyAndBranchScopeThroughEmployee
{
    public static function bootAppliesCompanyAndBranchScopeThroughEmployee(): void
    {
        static::addGlobalScope('company_and_branch_scope', function (Builder $builder): void {
            if ((app()->runningInConsole() && ! app()->runningUnitTests()) || ! auth()->check()) {
                return;
            }

            $user  = auth()->user();
            $model = $builder->getModel();
            $table = $model->getTable();

            if (! empty($user->saas_company_id) && Schema::hasColumn($table, 'saas_company_id')) {
                $builder->where($table . '.saas_company_id', (int) $user->saas_company_id);
            } elseif (! empty($user->saas_company_id) && Schema::hasColumn($table, 'company_id')) {
                $builder->where($table . '.company_id', (int) $user->saas_company_id);
            }

            if (($user->access_scope ?? 'all') === 'branch' && ! empty($user->branch_id)) {
                $builder->whereHas('employee', function (Builder $q) use ($user): void {
                    $q->where('branch_id', (int) $user->branch_id);
                });
            }
        });
    }
}
