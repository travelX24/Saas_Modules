<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_emails', function (Blueprint $t) {
            $t->id();
            $t->foreignId('template_id')->constrained('email_templates')->onDelete('cascade');
            $t->enum('send_type', ['immediate', 'scheduled'])->default('immediate');
            $t->enum('recipient_type', ['single', 'multiple'])->default('single');
            $t->json('recipient_company_ids')->nullable(); // For multiple companies
            $t->string('recipient_email', 190)->nullable(); // For single recipient
            $t->timestamp('scheduled_at')->nullable(); // For scheduled emails
            $t->enum('status', ['pending', 'processing', 'sent', 'failed'])->default('pending');
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('failed_at')->nullable();
            $t->text('error_message')->nullable();
            $t->json('variables_data')->nullable(); // Values for template variables
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->index('template_id');
            $t->index('status');
            $t->index('scheduled_at');
            $t->index('send_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};
