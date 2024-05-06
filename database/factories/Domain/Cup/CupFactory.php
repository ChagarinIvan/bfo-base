<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Cup;

use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupType;
use App\Models\Year;
use Illuminate\Database\Eloquent\Factories\Factory;

class CupFactory extends Factory
{
    protected $model = Cup::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'name' => $this->faker->name,
            'events_count' => $this->faker->numberBetween(1, 100),
            'year' => $this->faker->randomElement(Year::cases()),
            'type' => $this->faker->randomElement(CupType::cases()),
            'visible' => true,
            'active' => true,
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
