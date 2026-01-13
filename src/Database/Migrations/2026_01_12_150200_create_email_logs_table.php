<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('scheduled_email_id')->constrained('scheduled_emails')->onDelete('cascade');
            $t->string('recipient_email', 190);
            $t->string('subject', 255);
            $t->text('body'); // Final rendered body
            $t->enum('status', ['sent', 'failed', 'bounced'])->default('sent');
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('opened_at')->nullable();
            $t->timestamp('clicked_at')->nullable();
            $t->text('error_message')->nullable();
            $t->timestamps();

            $t->index('scheduled_email_id');
            $t->index('recipient_email');
            $t->index('status');
            $t->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
