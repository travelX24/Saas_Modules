<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $t) {
            $t->id();
            $t->string('name', 190);
            $t->string('subject', 255);
            $t->text('body'); // HTML content
            $t->json('variables')->nullable(); // Available variables like {company_name}, {expiry_date}
            $t->enum('type', ['subscription_expiry', 'update_notification', 'greeting', 'custom'])->default('custom');
            $t->boolean('is_active')->default(true);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();

            $t->index('type');
            $t->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
