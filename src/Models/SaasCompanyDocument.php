<?php

namespace Athka\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class SaasCompanyDocument extends Model
{
    protected $table = 'saas_company_documents';

    protected $fillable = [
        'company_id', 'type', 'file_path', 'original_name', 'mime', 'size', 'uploaded_by',
    ];

    public function company()
    {
        return $this->belongsTo(SaasCompany::class, 'company_id');
    }
}
