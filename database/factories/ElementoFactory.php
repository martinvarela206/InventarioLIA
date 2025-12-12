<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Elemento>
 */
class ElementoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly decide if the warranty should be expired or active
        // 50% chance of being acquired more than a year ago (likely expired)
        // 50% chance of being acquired recently (likely active)
        $isExpired = fake()->boolean();

        if ($isExpired) {
            $fechaAdquisicion = fake()->dateTimeBetween('-3 years', '-13 months');
        } else {
            $fechaAdquisicion = fake()->dateTimeBetween('-3 months', 'now');
        }

        $mesesGarantia = fake()->randomElement([3, 6, 12]);
        $fechaVencimiento = (clone $fechaAdquisicion)->modify("+{$mesesGarantia} months");

        return [
            'nro_lia' => fake()->unique()->bothify('LIA-#####'),
            'nro_unsj' => fake()->optional()->bothify('UNSJ-#####'),
            'tipo' => fake()->randomElement(['cpu', 'monitor', 'switch', 'router', 'impresora', 'teclado', 'mouse', 'proyector', 'disco', 'memoria', 'otro']),
            'descripcion' => fake()->sentence(3),
            'cantidad' => fake()->numberBetween(1, 10),
            'fecha_adquisicion' => $fechaAdquisicion,
            'fecha_vencimiento_garantia' => $fechaVencimiento,
        ];
    }
}
