<?php

declare(strict_types=1);

namespace Database\Factories\Domain\CupEvent;

use App\Domain\CupEvent\CupEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CupEventFactory extends Factory
{
    protected $model = CupEvent::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'cup_id' => $this->faker->numberBetween(1, 100),
            'event_id' => $this->faker->numberBetween(1, 100),
            'points' => $this->faker->numberBetween(1, 100),
        ];
    }
}
