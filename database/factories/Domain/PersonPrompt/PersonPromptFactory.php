<?php

declare(strict_types=1);

namespace Database\Factories\Domain\PersonPrompt;

use App\Domain\PersonPrompt\PersonPrompt;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonPromptFactory extends Factory
{
    protected $model = PersonPrompt::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'person_id' => $this->faker->numberBetween(1, 100),
            'prompt' => $this->faker->name,
            'metaphone' => $this->faker->name,
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
