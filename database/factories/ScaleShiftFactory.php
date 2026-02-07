<?php

namespace Database\Factories;

use App\Models\ScaleShift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScaleShift>
 */
class ScaleShiftFactory extends Factory
{
    protected $model = ScaleShift::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->date(),
            'name' => '06:00 - 12:00',
            'order' => 1,
        ];
    }

    /**
     * Define o turno como FOLGA.
     */
    public function folga(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'FOLGA',
            'order' => 5,
        ]);
    }

    /**
     * Define um turno específico.
     */
    public function turno(int $order): static
    {
        $turnos = [
            1 => '06:00 - 12:00',
            2 => '12:00 - 18:00',
            3 => '18:00 - 00:00',
            4 => '00:00 - 06:00',
            5 => 'FOLGA',
        ];

        return $this->state(fn (array $attributes) => [
            'name' => $turnos[$order] ?? 'Turno Padrão',
            'order' => $order,
        ]);
    }
}
