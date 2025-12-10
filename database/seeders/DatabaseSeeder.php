<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\Hippodrome;
use App\Models\Prediction;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin kullanıcı
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@atyarislari.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Test kullanıcıları
        User::create([
            'name' => 'Ahmet Yılmaz',
            'email' => 'ahmet@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '05321234567',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Mehmet Kaya',
            'email' => 'mehmet@test.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '05329876543',
            'email_verified_at' => now(),
        ]);

        // Abonelik Paketleri
        $standard = SubscriptionPlan::create([
            'name' => 'Standart Üyelik',
            'slug' => 'standard',
            'description' => 'Seçili şehir tahminlerine erişim',
            'price' => 199.99,
            'duration_days' => 30,
            'features' => [
                'Belirlenen bir şehirdeki kupon tahminleri',
                'Basit tahmin analizi',
                'Günlük tahmin bildirimleri',
                'Geçmiş tahmin arşivi',
            ],
            'is_active' => true,
        ]);

        $premium = SubscriptionPlan::create([
            'name' => 'Premium Üyelik',
            'slug' => 'premium',
            'description' => 'Tüm şehirlerdeki detaylı tahminler',
            'price' => 399.99,
            'duration_days' => 30,
            'features' => [
                'Tüm şehirlerdeki kupon tahminleri',
                'Detaylı analiz ve istatistikler',
                'Özel BANKO kuponlar',
                'Canlı WhatsApp desteği',
                'Yarış öncesi bilgilendirme',
                'Uzman yorumları',
                'Başarı oranı takibi',
            ],
            'is_active' => true,
        ]);

        // Hipodromlar
        $veliefendi = Hippodrome::create([
            'name' => 'Veliefendi Hipodromu',
            'city' => 'İstanbul',
            'slug' => 'veliefendi',
            'is_active' => true,
        ]);

        $izmir = Hippodrome::create([
            'name' => 'İzmir Hipodromu',
            'city' => 'İzmir',
            'slug' => 'izmir',
            'is_active' => true,
        ]);

        $ankara = Hippodrome::create([
            'name' => 'Ankara Hipodromu',
            'city' => 'Ankara',
            'slug' => 'ankara',
            'is_active' => true,
        ]);

        $adana = Hippodrome::create([
            'name' => 'Adana Hipodromu',
            'city' => 'Adana',
            'slug' => 'adana',
            'is_active' => true,
        ]);

        $bursa = Hippodrome::create([
            'name' => 'Bursa Hipodromu',
            'city' => 'Bursa',
            'slug' => 'bursa',
            'is_active' => true,
        ]);

        // Örnek Tahminler
        Prediction::create([
            'hippodrome_id' => $veliefendi->id,
            'race_date' => today(),
            'race_time' => '14:00',
            'race_number' => 1,
            'access_level' => 'standard',
            'race_title' => '1. Koşu - Safkan Araplar',
            'basic_prediction' => "Günün ilk koşusunda favori 3 numara YILDIZ. Son performansları göz önüne alındığında bu koşuda şansını deneyebilir. Alternatif: 7 numara.",
            'detailed_analysis' => null,
            'banker_tips' => null,
            'statistics' => [
                'track_condition' => 'İyi',
                'weather' => 'Açık',
                'total_horses' => 12,
            ],
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
            'prediction_result' => 'pending',
        ]);

        Prediction::create([
            'hippodrome_id' => $veliefendi->id,
            'race_date' => today(),
            'race_time' => '14:30',
            'race_number' => 2,
            'access_level' => 'premium',
            'race_title' => '2. Koşu - İngiliz Yarım Kanlar',
            'basic_prediction' => "Premium üyelere özel detaylı analiz mevcuttur.",
            'detailed_analysis' => "Bu koşuda 2 numara ŞAMPIYON açık ara favori. Son 3 koşudaki performansı mükemmel. Jokeyi ile uyumu da çok iyi. Pist koşulları da lehinde.\n\nDikkat edilmesi gerekenler:\n- Start pozisyonu avantajlı\n- Rakiplerinden deneyimli\n- Form grafiği yükseliş trendinde\n\nRisk Analizi: Düşük risk",
            'banker_tips' => "🎯 BANKO: 2 numara ŞAMPIYON\n💰 Yedek Banko: 5 numara KAHRAMAN\n⭐ Sürpriz Aday: 9 numara CESUR",
            'statistics' => [
                'track_condition' => 'İyi',
                'weather' => 'Açık',
                'total_horses' => 14,
                'favorite_odds' => '2.50',
                'track_record' => '1:23.45',
            ],
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
            'prediction_result' => 'pending',
        ]);

        Prediction::create([
            'hippodrome_id' => $izmir->id,
            'race_date' => today(),
            'race_time' => '15:00',
            'race_number' => 3,
            'access_level' => 'standard',
            'race_title' => '3. Koşu - KPA',
            'basic_prediction' => "İzmir'deki bu koşuda 4 ve 6 numaralar ön plana çıkıyor. Pist koşullarına göre 4 numara daha avantajlı görünüyor.",
            'status' => 'published',
            'published_at' => now(),
            'created_by' => $admin->id,
            'prediction_result' => 'pending',
        ]);

        // Yarın için draft tahmin
        Prediction::create([
            'hippodrome_id' => $ankara->id,
            'race_date' => today()->addDay(),
            'race_time' => '13:30',
            'race_number' => 1,
            'access_level' => 'premium',
            'race_title' => 'Ankara - 1. Koşu',
            'basic_prediction' => 'Tahmin hazırlanıyor...',
            'status' => 'draft',
            'published_at' => null,
            'created_by' => $admin->id,
        ]);

        $this->command->info('✅ Veritabanı örnek verilerle dolduruldu!');
        $this->command->info('📧 Admin Email: admin@atyarislari.com');
        $this->command->info('🔑 Admin Şifre: password');
    }
}
