<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Event;

use App\Domain\Event\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'date' => $this->faker->date,
            'competition_id' =>  $this->faker->numberBetween(1, 100),
            'file' => '',
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
