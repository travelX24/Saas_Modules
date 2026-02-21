<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saas_company_id')->index();
            $table->string('name', 190);
            $table->string('code', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['saas_company_id', 'name'], 'branches_company_name_unique');
            $table->unique(['saas_company_id', 'code'], 'branches_company_code_unique');

            $table->foreign('saas_company_id')
                ->references('id')
                ->on('saas_companies')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
