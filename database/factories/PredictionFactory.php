<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hippodrome;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PredictionFactory extends Factory
{
    protected $model = Prediction::class;

    public function definition(): array
    {
        $isPast = $this->faker->boolean(40); // %40 geçmiş tahmin
        $date = $isPast ? $this->faker->dateTimeBetween('-1 week', '-1 day') : $this->faker->dateTimeBetween('now', '+1 week');

        $result = 'pending';
        if ($isPast) {
            $result = $this->faker->randomElement(['won', 'lost']);
        }

        return [
            'hippodrome_id' => Hippodrome::factory(),
            'created_by' => User::factory(), // veya admin ID atanabilir seeder'da
            'race_date' => $date,
            'race_time' => $this->faker->time('H:i'),
            'race_number' => $this->faker->numberBetween(1, 9),
            'race_title' => $this->faker->optional()->randomElement(['Gazi Koşusu', 'Cumhuriyet Koşusu', 'Maiden', 'Handikap 15']),

            'access_level' => $this->faker->randomElement(['standard', 'premium']),

            'basic_prediction' => $this->faker->paragraph(3),
            'detailed_analysis' => $this->faker->paragraphs(2, true),
            'banker_tips' => "1. Ayak: " . $this->faker->numberBetween(1, 10) . " Numara BANKO\nSürpriz: " . $this->faker->numberBetween(1, 10),

            'statistics' => [
                'zemin' => $this->faker->randomElement(['Kum', 'Çim', 'Sentetik']),
                'mesafe' => $this->faker->randomElement(['1200m', '1400m', '1600m', '2000m', '2400m']),
                'hava_durumu' => $this->faker->randomElement(['Güneşli', 'Yağmurlu', 'Kapalı']),
            ],

            'status' => 'published',
            'prediction_result' => $result,
            'winning_horse' => $isPast ? $this->faker->name . ' (' . $this->faker->numberBetween(1, 15) . ')' : null,
            'winning_odds' => $isPast ? $this->faker->randomFloat(2, 1.05, 15.00) : null,
            'published_at' => now(),
        ];
    }
}
