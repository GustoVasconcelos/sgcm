<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'start_time' => $this->faker->randomElement(['06:00', '08:00', '10:00', '12:00', '14:00']),
            'duration' => $this->faker->randomElement([30, 60, 90, 120]),
            'custom_info' => null,
            'notes' => null,
            'status_mago' => false,
            'status_verification' => false,
        ];
    }

    /**
     * Sábado específico
     */
    public function onSaturday($date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
}
