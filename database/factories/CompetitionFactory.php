<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'from' => $this->faker->date,
            'to' => $this->faker->date,
        ];
    }
}
