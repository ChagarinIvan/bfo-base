<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Rank;

use App\Domain\Rank\Rank;
use Illuminate\Database\Eloquent\Factories\Factory;

class RankFactory extends Factory
{
    protected $model = Rank::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'person_id' => $this->faker->numberBetween(1, 100),
            'event_id' => $this->faker->numberBetween(1, 100),
            'rank' => 'ii',
            'start_date' => $this->faker->date,
            'finish_date' => $this->faker->date,
            'activated_date' => $this->faker->date,
        ];
    }
}
