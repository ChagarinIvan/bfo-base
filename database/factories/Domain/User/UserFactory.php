<?php

declare(strict_types=1);

namespace Database\Factories\Domain\User;

use App\Domain\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'email' => $this->faker->email,
            'password' => $this->faker->password,
        ];
    }
}
