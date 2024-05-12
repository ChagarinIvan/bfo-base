<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Group;

use App\Domain\Group\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 100),
            'name' => $this->faker->name,
        ];
    }
}
