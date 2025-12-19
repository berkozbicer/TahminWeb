<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\SubscriptionPlan;

class SubscriptionPlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\SubscriptionPlan>
     */
    protected $model = SubscriptionPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Standart Paket', 'Premium Paket', 'VIP Paket']),
            'slug' => fn (array $attributes) => Str::slug($attributes['name']),
            'description' => $this->faker->sentence(8),
            'price' => $this->faker->randomFloat(2, 49, 499),
            'duration_days' => $this->faker->randomElement([7, 14, 30]),
            'features' => [
                'Günlük tahminler',
                $this->faker->boolean() ? 'Detaylı analizler' : 'Temel analizler',
                $this->faker->boolean() ? 'Bildirim desteği' : 'E-posta bilgilendirme',
            ],
            'is_active' => true,
        ];
    }
}
