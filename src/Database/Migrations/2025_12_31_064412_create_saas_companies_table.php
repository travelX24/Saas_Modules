<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_companies', function (Blueprint $t) {
            $t->id();

            // Tab 1: Basic
            $t->string('legal_name_ar', 190);
            $t->string('legal_name_en', 190)->nullable();

            $t->string('slug', 190)->unique();
            $t->string('primary_domain', 190)->nullable()->unique();

            $t->enum('company_type', ['individual', 'foundation', 'company']);

            $t->string('logo_path')->nullable();

            $t->string('main_industry', 190)->nullable();
            $t->json('sub_industries')->nullable();
            $t->text('bio')->nullable();

            // Tab 2: Contact
            $t->string('official_email', 190)->nullable();
            $t->string('phone_1', 50)->nullable();
            $t->string('phone_2', 50)->nullable();

            // Tab 2: Address
            $t->string('country', 120)->nullable();
            $t->string('city', 120)->nullable();
            $t->string('region', 120)->nullable();
            $t->string('address_line', 255)->nullable();
            $t->string('postal_code', 30)->nullable();

            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lng', 10, 7)->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_companies');
    }
};
