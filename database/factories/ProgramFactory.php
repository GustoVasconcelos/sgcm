<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'default_duration' => $this->faker->randomElement([30, 60, 90, 120]),
            'color' => $this->faker->hexColor(),
        ];
    }
}
