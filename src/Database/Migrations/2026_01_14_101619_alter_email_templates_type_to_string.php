<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE email_templates MODIFY type VARCHAR(50) NOT NULL DEFAULT 'custom'");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE email_templates
            MODIFY type ENUM('subscription_expiry','update_notification','greeting','custom')
            NOT NULL DEFAULT 'custom'
        ");
    }
};
