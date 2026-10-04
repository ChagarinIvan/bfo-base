<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Distance;

use App\Domain\Distance\Distance;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Distance> */
class DistanceFactory extends Factory
{
    protected $model = Distance::class;

    public function definition(): array
    {
        return [
            'group_id' => $this->faker->numberBetween(1, 100),
            'event_id' => $this->faker->numberBetween(1, 100),
            'length' => $this->faker->numberBetween(1000, 2000),
            'points' => 1000,
            'disqual' => false,
        ];
    }
}
