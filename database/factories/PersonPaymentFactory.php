<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PersonPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonPaymentFactory extends Factory
{
    protected $model = PersonPayment::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'person_id' => $this->faker->numberBetween(1, 100),
            'year' => $this->faker->year,
            'date' => $this->faker->date,
            'created' => AuthFactory::random(),
            'updated' =>  AuthFactory::random(),
        ];
    }
}
