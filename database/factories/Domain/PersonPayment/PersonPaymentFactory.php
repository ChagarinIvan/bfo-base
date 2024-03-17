<?php

declare(strict_types=1);

namespace Database\Factories\Domain\PersonPayment;

use App\Domain\PersonPayment\PersonPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonPaymentFactory extends Factory
{
    protected $model = PersonPayment::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'person_id' => $this->faker->numberBetween(1, 100),
            'year' => (int) $this->faker->year,
            'date' => $this->faker->date,
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
