<?php

namespace Database\Factories;

use App\Models\Vacation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacationFactory extends Factory
{
    protected $model = Vacation::class;

    public function definition(): array
    {
        $year = date('Y');
        $startDate = $this->faker->dateTimeBetween("$year-01-01", "$year-06-30");
        $endDate = (clone $startDate)->modify('+29 days');

        return [
            'user_id' => User::factory(),
            'year' => $year,
            'mode' => '30_dias',
            'status' => 'aprovado',
            'period_1_start' => $startDate->format('Y-m-d'),
            'period_1_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Férias no modo 15+15
     */
    public function split(): static
    {
        return $this->state(fn (array $attributes) => [
            'mode' => '15_15',
            'period_2_start' => date('Y') . '-12-01',
            'period_2_end' => date('Y') . '-12-15',
        ]);
    }
}
