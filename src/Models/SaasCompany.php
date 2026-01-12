<?php

namespace Athka\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class SaasCompany extends Model
{
    protected $table = 'saas_companies';

    protected $fillable = [
        'legal_name_ar', 'legal_name_en',

        // ✅ موجود في الجدول
        'slug',
        'primary_domain',

        'company_type',
        'logo_path',
        'main_industry', 'sub_industries', 'bio',
        'official_email', 'phone_1', 'phone_2',
        'country', 'city', 'region', 'address_line', 'postal_code',
        'lat', 'lng',
    ];

    protected $casts = [
        'sub_industries' => 'array',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    protected static function boot()
    {
        parent::boot();

        // حذف جميع المستخدمين المرتبطين بالشركة عند حذف الشركة
        static::deleting(function ($company) {
            $company->users()->delete();
        });
    }

    public function settings()
    {
        return $this->hasOne(SaasCompanyOtherinfo::class, 'company_id');
    }

    public function documents()
    {
        return $this->hasMany(SaasCompanyDocument::class, 'company_id');
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'saas_company_id');
    }
}
