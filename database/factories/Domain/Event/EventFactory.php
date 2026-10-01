<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Event;

use App\Domain\Event\Event;
use App\Domain\Event\EventProcessingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Event> */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'date' => $this->faker->date,
            'competition_id' =>  $this->faker->numberBetween(1, 100),
            'file' => '',
            'processing_status' => EventProcessingStatus::READY,
            'processing_token' => (string) Str::uuid(),
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
