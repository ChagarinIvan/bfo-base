<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Person;

use App\Domain\Person\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

final class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'lastname' => $this->faker->lastName,
            'firstname' => $this->faker->firstName,
            'birthday' => $this->faker->date,
            'club_id' => null,
            'from_base' => false,
            'created_at' => $this->faker->date,
            'created_by' => $this->faker->numberBetween(1, 100),
            'updated_at' => $this->faker->date,
            'updated_by' => $this->faker->numberBetween(1, 100),
        ];
    }
}
