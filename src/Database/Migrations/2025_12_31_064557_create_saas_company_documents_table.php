<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saas_company_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('saas_companies')
                ->cascadeOnDelete();

            // نوع المستند: cr, vat, license, moa, owner_id, passport .. إلخ
            $table->string('type', 50);

            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_company_documents');
    }
};
