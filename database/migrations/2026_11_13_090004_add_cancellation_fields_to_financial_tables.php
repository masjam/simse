<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify payments status to string to support 'Batal' smoothly
        if (Schema::hasTable('payments')) {
            // Using DB statement for enum expansion in MySQL or string column
            try {
                DB::statement("ALTER TABLE payments MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Menunggu Verifikasi'");
            } catch (Throwable $e) {
                // In SQLite or test environments where ALTER TABLE MODIFY might differ
            }

            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'canceled_at')) {
                    $table->timestamp('canceled_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('payments', 'canceled_by')) {
                    $table->foreignUuid('canceled_by')->nullable()->after('canceled_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('payments', 'cancel_reason')) {
                    $table->text('cancel_reason')->nullable()->after('canceled_by');
                }
            });
        }

        // Modify wallet_transactions status to string to support 'Batal' smoothly
        if (Schema::hasTable('wallet_transactions')) {
            try {
                DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Sukses'");
            } catch (Throwable $e) {
                // In SQLite or test environments
            }

            Schema::table('wallet_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('wallet_transactions', 'canceled_at')) {
                    $table->timestamp('canceled_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('wallet_transactions', 'canceled_by')) {
                    $table->foreignUuid('canceled_by')->nullable()->after('canceled_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn('wallet_transactions', 'cancel_reason')) {
                    $table->text('cancel_reason')->nullable()->after('canceled_by');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'canceled_by')) {
                    $table->dropForeign(['canceled_by']);
                    $table->dropColumn('canceled_by');
                }
                if (Schema::hasColumn('payments', 'canceled_at')) {
                    $table->dropColumn('canceled_at');
                }
                if (Schema::hasColumn('payments', 'cancel_reason')) {
                    $table->dropColumn('cancel_reason');
                }
            });
        }

        if (Schema::hasTable('wallet_transactions')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                if (Schema::hasColumn('wallet_transactions', 'canceled_by')) {
                    $table->dropForeign(['canceled_by']);
                    $table->dropColumn('canceled_by');
                }
                if (Schema::hasColumn('wallet_transactions', 'canceled_at')) {
                    $table->dropColumn('canceled_at');
                }
                if (Schema::hasColumn('wallet_transactions', 'cancel_reason')) {
                    $table->dropColumn('cancel_reason');
                }
            });
        }
    }
};
