<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Distance;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistanceFactory extends Factory
{
    protected $model = Distance::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'group_id' => $this->faker->numberBetween(1, 100),
            'event_id' => $this->faker->numberBetween(1, 100),
            'length' => $this->faker->numberBetween(1000, 2000),
            'points' => 1000,
            'disqual' => false,
        ];
    }
}
