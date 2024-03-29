<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Competition;

use App\Domain\Competition\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'from' => $this->faker->date,
            'to' => $this->faker->date,
            'active' => true,
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
