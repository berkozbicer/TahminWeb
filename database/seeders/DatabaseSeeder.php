<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Hippodrome;
use App\Models\Prediction;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Yönetici Hesabı Oluştur
        $admin = User::factory()->admin()->create([
            'name' => 'Site Yöneticisi',
            'password' => bcrypt('password'), // Şifre: password
        ]);

        // 2. Normal Test Kullanıcısı
        User::factory()->create([
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // 3. Abonelik Paketlerini Oluştur (Sabit veriler önemli)
        $standardPlan = SubscriptionPlan::create([
            'name' => 'Standart Paket',
            'slug' => 'standart',
            'description' => 'Başlangıç seviyesi için ideal tahminler.',
            'price' => 199.00,
            'duration_days' => 30,
            'features' => ['Günlük Tahminler', 'Temel Analizler', 'Yarış Sonuçları'],
            'is_active' => true,
        ]);

        $premiumPlan = SubscriptionPlan::create([
            'name' => 'Premium Paket',
            'slug' => 'premium',
            'description' => 'Profesyoneller için detaylı analiz ve banko kuponlar.',
            'price' => 399.00,
            'duration_days' => 30,
            'features' => ['Her Şey Dahil', 'Detaylı Yapay Zeka Analizi', 'Banko Kuponlar', '7/24 Destek', 'WhatsApp Bildirim'],
            'is_active' => true,
        ]);

        // 4. Hipodromları Oluştur (Factory'deki listeden rastgele 5 tane)
        $hippodromes = Hippodrome::factory(5)->create();

        // 5. Tahminleri Oluştur
        foreach ($hippodromes as $hippodrome) {
            // Her hipodrom için hem geçmiş hem gelecek tahminler oluştur
            Prediction::factory(3)->create([
                'hippodrome_id' => $hippodrome->id,
                'created_by' => $admin->id,
                'access_level' => 'standard'
            ]);

            Prediction::factory(3)->create([
                'hippodrome_id' => $hippodrome->id,
                'created_by' => $admin->id,
                'access_level' => 'premium'
            ]);
        }
    }
}
