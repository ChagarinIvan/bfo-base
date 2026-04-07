<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Competition;

use App\Domain\Competition\Competition;
use App\Models\Year;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        $year = $this->faker->randomElement(Year::cases());

        $from = Carbon::create($year->value)
            ->startOfYear()
            ->addDays($this->faker->numberBetween(0, 364));

        $to = (clone $from)->addDays($this->faker->numberBetween(0, 10));

        return [
            'id' => $this->faker->numberBetween(1, 100),
            'name' => $this->faker->name,
            'description' => $this->faker->name,
            'from' => $from,
            'to' => $to,
            'active' => true,
            'mass' => false,
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
