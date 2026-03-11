<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('saas_company_otherinfo', function (Blueprint $table) {
            $table->json('excluded_leave_contract_types')->nullable()->after('default_annual_leave_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saas_company_otherinfo', function (Blueprint $table) {
            $table->dropColumn('excluded_leave_contract_types');
        });
    }
};
