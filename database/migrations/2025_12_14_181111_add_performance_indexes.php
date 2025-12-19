<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Bu migration performans için kritik index'leri ekler.
     */
    public function up(): void
    {
        // Predictions tablosu için index'ler
        Schema::table('predictions', function (Blueprint $table) {
            // Yayınlanmış tahminler için composite index
            $table->index(['status', 'published_at'], 'idx_predictions_published');
            
            // Tarih bazlı sorgular için
            $table->index('race_date', 'idx_predictions_date');
            
            // Hipodrom bazlı sorgular için
            $table->index('hippodrome_id', 'idx_predictions_hippodrome');
            
            // Erişim seviyesi ve durum için
            $table->index(['access_level', 'status'], 'idx_predictions_access');
        });

        // Subscriptions tablosu için index'ler
        Schema::table('subscriptions', function (Blueprint $table) {
            // Aktif abonelik sorguları için composite index (en kritik)
            $table->index(['user_id', 'status', 'expires_at'], 'idx_subscriptions_active');
            
            // Kullanıcı bazlı sorgular için
            $table->index('user_id', 'idx_subscriptions_user');
            
            // Plan bazlı sorgular için
            $table->index('subscription_plan_id', 'idx_subscriptions_plan');
        });

        // Payment logs tablosu için index'ler
        Schema::table('payment_logs', function (Blueprint $table) {
            // Transaction ID lookup için (PayTR callback'lerde kullanılıyor)
            $table->index('transaction_id', 'idx_payment_logs_transaction');
            
            // Kullanıcı bazlı sorgular için
            $table->index('user_id', 'idx_payment_logs_user');
            
            // Status bazlı sorgular için
            $table->index('status', 'idx_payment_logs_status');
            
            // Tarih bazlı sorgular için
            $table->index('created_at', 'idx_payment_logs_created');
        });

        // Users tablosu için index'ler
        Schema::table('users', function (Blueprint $table) {
            // Email lookup için (zaten unique index var ama kontrol edelim)
            if (!$this->hasIndex('users', 'users_email_unique')) {
                $table->unique('email', 'users_email_unique');
            }
            
            // Role bazlı sorgular için
            $table->index('role', 'idx_users_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndex('idx_predictions_published');
            $table->dropIndex('idx_predictions_date');
            $table->dropIndex('idx_predictions_hippodrome');
            $table->dropIndex('idx_predictions_access');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex('idx_subscriptions_active');
            $table->dropIndex('idx_subscriptions_user');
            $table->dropIndex('idx_subscriptions_plan');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropIndex('idx_payment_logs_transaction');
            $table->dropIndex('idx_payment_logs_user');
            $table->dropIndex('idx_payment_logs_status');
            $table->dropIndex('idx_payment_logs_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = $connection->select(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$database, $table, $index]
        );
        
        return $result[0]->count > 0;
    }
};
