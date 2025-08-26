<?php

namespace Database\Factories;

use App\Models\Concepto;
use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Adeudo>
 */
class AdeudoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 100, 2000);
        $pagado = $this->faker->randomFloat(2, 0, $total);
        $pendiente = $total - $pagado;
        
        $fechaInicio = $this->faker->dateTimeBetween('-3 months', 'now');
        $fechaVencimiento = $this->faker->dateTimeBetween($fechaInicio, '+3 months');
        
        $estados = ['pendiente', 'pagado', 'vencido'];
        $estado = $this->faker->randomElement($estados);
        
        if ($estado === 'pagado') {
            $pagado = $total;
            $pendiente = 0;
        } elseif ($estado === 'vencido' && $pendiente > 0) {
            // Mantener valores calculados arriba
        }

        return [
            'concepto_id' => Concepto::factory(),
            'estudiante_id' => Estudiante::factory(),
            'estado' => $estado,
            'pendiente' => $pendiente,
            'pagado' => $pagado,
            'total' => $total,
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento
        ];
    }
}
