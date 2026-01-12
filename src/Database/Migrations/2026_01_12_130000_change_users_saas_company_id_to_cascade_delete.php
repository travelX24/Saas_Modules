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
        Schema::table('users', function (Blueprint $table) {
            // حذف القيد الخارجي القديم
            $table->dropForeign(['saas_company_id']);
            
            // إضافة قيد خارجي جديد مع cascade delete
            $table->foreign('saas_company_id')
                ->references('id')
                ->on('saas_companies')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // حذف القيد الخارجي الجديد
            $table->dropForeign(['saas_company_id']);
            
            // إعادة القيد الخارجي القديم مع null on delete
            $table->foreign('saas_company_id')
                ->references('id')
                ->on('saas_companies')
                ->onDelete('set null');
        });
    }
};
