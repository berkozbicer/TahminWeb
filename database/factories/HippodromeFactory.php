<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hippodrome;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HippodromeFactory extends Factory
{
    protected $model = Hippodrome::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Veliefendi', 'Osmangazi', 'Yeşiloba', 'Şirinyer',
            '75. Yıl', 'Kartepe', 'Kocaeli', 'Diyarbakır', 'Elazığ', 'Antalya'
        ]);

        $cities = [
            'Veliefendi' => 'İstanbul',
            'Osmangazi' => 'Bursa',
            'Yeşiloba' => 'Adana',
            'Şirinyer' => 'İzmir',
            '75. Yıl' => 'Ankara',
            'Kartepe' => 'Kocaeli',
            'Kocaeli' => 'Kocaeli',
            'Diyarbakır' => 'Diyarbakır',
            'Elazığ' => 'Elazığ',
            'Antalya' => 'Antalya'
        ];

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'city' => $cities[$name] ?? $this->faker->city,
            'is_active' => true,
        ];
    }
}
