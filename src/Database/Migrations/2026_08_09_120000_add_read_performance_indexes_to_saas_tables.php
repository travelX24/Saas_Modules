<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saas_companies', function (Blueprint $table) {
            if (! $this->hasIndex('saas_companies', 'idx_companies_active_created')) {
                $table->index(['is_active', 'created_at'], 'idx_companies_active_created');
            }
        });

        Schema::table('scheduled_emails', function (Blueprint $table) {
            if (! $this->hasIndex('scheduled_emails', 'idx_scheduled_emails_created_at')) {
                $table->index('created_at', 'idx_scheduled_emails_created_at');
            }

            if (! $this->hasIndex('scheduled_emails', 'idx_scheduled_emails_status_created')) {
                $table->index(['status', 'created_at'], 'idx_scheduled_emails_status_created');
            }
        });

        Schema::table('email_templates', function (Blueprint $table) {
            if (! $this->hasIndex('email_templates', 'idx_email_templates_created_at')) {
                $table->index('created_at', 'idx_email_templates_created_at');
            }

            if (! $this->hasIndex('email_templates', 'idx_email_templates_active_created')) {
                $table->index(['is_active', 'created_at'], 'idx_email_templates_active_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if ($this->hasIndex('email_templates', 'idx_email_templates_active_created')) {
                $table->dropIndex('idx_email_templates_active_created');
            }

            if ($this->hasIndex('email_templates', 'idx_email_templates_created_at')) {
                $table->dropIndex('idx_email_templates_created_at');
            }
        });

        Schema::table('scheduled_emails', function (Blueprint $table) {
            if ($this->hasIndex('scheduled_emails', 'idx_scheduled_emails_status_created')) {
                $table->dropIndex('idx_scheduled_emails_status_created');
            }

            if ($this->hasIndex('scheduled_emails', 'idx_scheduled_emails_created_at')) {
                $table->dropIndex('idx_scheduled_emails_created_at');
            }
        });

        Schema::table('saas_companies', function (Blueprint $table) {
            if ($this->hasIndex('saas_companies', 'idx_companies_active_created')) {
                $table->dropIndex('idx_companies_active_created');
            }
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'mysql') {
            $database = $connection->getDatabaseName();
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName]
            );

            return (int) $result[0]->count > 0;
        }

        if ($connection->getDriverName() === 'sqlite') {
            return count($connection->select(
                "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?",
                [$table, $indexName]
            )) > 0;
        }

        return false;
    }
};
