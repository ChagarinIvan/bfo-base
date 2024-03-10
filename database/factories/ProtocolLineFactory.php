<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProtocolLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProtocolLineFactory extends Factory
{
    protected $model = ProtocolLine::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'serial_number' => $this->faker->numberBetween(1, 100),
            'lastname' => $this->faker->lastName,
            'firstname' => $this->faker->firstName,
            'club' => $this->faker->name,
            'year' => $this->faker->year,
            'rank' => 'ii',
            'runner_number' => $this->faker->numberBetween(101, 200),
            'time' => '00:16:23',
            'place' => 1,
            'complete_rank' => 'i',
            'points' => 100,
            'distance_id' => 1,
            'person_id' => null,
            'prepared_line' => $this->faker->lastName,
            'vk' => false,
            'activate_rank' => $this->faker->date,
        ];
    }
}
