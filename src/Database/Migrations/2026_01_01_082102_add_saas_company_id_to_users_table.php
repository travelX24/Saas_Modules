<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('saas_company_id')
                ->nullable()
                ->after('id')
                ->constrained('saas_companies')
                ->nullOnDelete();

            $t->index('saas_company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('saas_company_id');
        });
    }
};
