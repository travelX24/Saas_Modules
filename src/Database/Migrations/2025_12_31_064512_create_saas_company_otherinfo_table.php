<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_company_otherinfo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('saas_companies')
                ->cascadeOnDelete();

            // ✅ Numbers
            $table->string('license_number')->nullable(); // رقم الترخيص
            $table->string('tax_number')->nullable();     // الرقم الضريبي
            $table->string('cr_number')->nullable();      // رقم السجل التجاري

            // ✅ Subscription
            $table->date('subscription_starts_at')->nullable();
            $table->date('subscription_ends_at')->nullable();

            $table->unsignedInteger('allowed_users')->default(1);

            // ✅ UI/Locale
            $table->string('timezone')->default('Asia/Aden');
            $table->string('default_locale')->default('ar'); // ar/en
            $table->string('datetime_format')->default('Y-m-d H:i');

            $table->timestamps();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_company_otherinfo');
    }
};
