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
        // Indexes لجدول saas_companies
        Schema::table('saas_companies', function (Blueprint $table) {
            // Index للتاريخ (للاستعلامات حسب الشهر)
            $table->index('created_at', 'idx_companies_created_at');

            // Indexes للفلاتر
            $table->index('main_industry', 'idx_companies_main_industry');
            $table->index('city', 'idx_companies_city');
            $table->index('country', 'idx_companies_country');
            $table->index('company_type', 'idx_companies_company_type');
        });

        // Indexes لجدول saas_company_otherinfo
        Schema::table('saas_company_otherinfo', function (Blueprint $table) {
            // Index للاشتراكات (للاستعلامات السريعة)
            $table->index('subscription_ends_at', 'idx_otherinfo_subscription_ends_at');

            // Index مركب للشركات النشطة (company_id + subscription_ends_at)
            $table->index(['company_id', 'subscription_ends_at'], 'idx_otherinfo_company_subscription');
        });

        // Indexes لجدول users (إن لم يكن موجوداً)
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Index للتاريخ (للاستعلامات حسب الشهر)
                if (! $this->hasIndex('users', 'idx_users_created_at')) {
                    $table->index('created_at', 'idx_users_created_at');
                }

                // Index مركب للاستعلامات السريعة (saas_company_id + created_at)
                if (! $this->hasIndex('users', 'idx_users_company_created')) {
                    $table->index(['saas_company_id', 'created_at'], 'idx_users_company_created');
                }
            });
        }

        // Indexes لجدول language_lines (إن كان موجوداً)
        if (Schema::hasTable('language_lines')) {
            Schema::table('language_lines', function (Blueprint $table) {
                // Index للبحث في key
                if (! $this->hasIndex('language_lines', 'idx_language_lines_key')) {
                    $table->index('key', 'idx_language_lines_key');
                }

                // Index مركب للبحث (group + key)
                if (! $this->hasIndex('language_lines', 'idx_language_lines_group_key')) {
                    $table->index(['group', 'key'], 'idx_language_lines_group_key');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saas_companies', function (Blueprint $table) {
            $table->dropIndex('idx_companies_created_at');
            $table->dropIndex('idx_companies_main_industry');
            $table->dropIndex('idx_companies_city');
            $table->dropIndex('idx_companies_country');
            $table->dropIndex('idx_companies_company_type');
        });

        Schema::table('saas_company_otherinfo', function (Blueprint $table) {
            $table->dropIndex('idx_otherinfo_subscription_ends_at');
            $table->dropIndex('idx_otherinfo_company_subscription');
        });

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if ($this->hasIndex('users', 'idx_users_created_at')) {
                    $table->dropIndex('idx_users_created_at');
                }
                if ($this->hasIndex('users', 'idx_users_company_created')) {
                    $table->dropIndex('idx_users_company_created');
                }
            });
        }

        if (Schema::hasTable('language_lines')) {
            Schema::table('language_lines', function (Blueprint $table) {
                if ($this->hasIndex('language_lines', 'idx_language_lines_key')) {
                    $table->dropIndex('idx_language_lines_key');
                }
                if ($this->hasIndex('language_lines', 'idx_language_lines_group_key')) {
                    $table->dropIndex('idx_language_lines_group_key');
                }
            });
        }
    }

    /**
     * التحقق من وجود index
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        if ($connection->getDriverName() === 'mysql') {
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics 
                WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName]
            );

            return $result[0]->count > 0;
        }

        // للـ SQLite
        if ($connection->getDriverName() === 'sqlite') {
            $indexes = $connection->select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name=? AND name=?", [$table, $indexName]);

            return count($indexes) > 0;
        }

        return false;
    }
};
