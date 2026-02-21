<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'access_scope')) {
                    $table->string('access_scope', 20)->default('all')->after('employee_id');
                }

                if (! Schema::hasColumn('users', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('access_scope');
                    $table->index('branch_id');
                }
            });

            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'branch_id')) {
                    $table->foreign('branch_id')
                        ->references('id')
                        ->on('branches')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (! Schema::hasColumn('employees', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('saas_company_id');
                    $table->index('branch_id');
                }
            });

            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'branch_id')) {
                    $table->foreign('branch_id')
                        ->references('id')
                        ->on('branches')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'branch_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }

        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'branch_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('branch_id');
                });
            }

            if (Schema::hasColumn('users', 'access_scope')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('access_scope');
                });
            }
        }
    }
};
