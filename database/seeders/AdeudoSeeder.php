<?php

namespace Database\Seeders;

use App\Models\Adeudo;
use App\Models\Concepto;
use App\Models\Estudiante;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdeudoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos conceptos y estudiantes existentes
        $conceptos = Concepto::all();
        $estudiantes = Estudiante::all();

        if ($conceptos->count() > 0 && $estudiantes->count() > 0) {
            // Crear 20 adeudos usando datos existentes
            for ($i = 0; $i < 20; $i++) {
                $concepto = $conceptos->random();
                $estudiante = $estudiantes->random();
                
                $total = $concepto->costo;
                $estado = fake()->randomElement(['pendiente', 'pagado', 'vencido']);
                
                if ($estado === 'pagado') {
                    $pagado = $total;
                    $pendiente = 0;
                } else {
                    $pagado = fake()->randomFloat(2, 0, $total);
                    $pendiente = $total - $pagado;
                }
                
                $fechaInicio = fake()->dateTimeBetween('-3 months', 'now');
                $fechaVencimiento = fake()->dateTimeBetween($fechaInicio, '+3 months');

                Adeudo::create([
                    'concepto_id' => $concepto->id,
                    'estudiante_id' => $estudiante->id,
                    'estado' => $estado,
                    'pendiente' => $pendiente,
                    'pagado' => $pagado,
                    'total' => $total,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_vencimiento' => $fechaVencimiento
                ]);
            }
        } else {
            // Si no hay datos existentes, crear usando factory
            Adeudo::factory(15)->create();
        }
    }
}
