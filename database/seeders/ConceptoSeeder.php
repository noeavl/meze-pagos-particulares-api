<?php

namespace Database\Seeders;

use App\Models\Concepto;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConceptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conceptos = [
            [
                'nombre' => 'Ficha',
                'costo' => 100.00,
                'periodo' => 'pago_unico',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Credencial',
                'costo' => 50.00,
                'periodo' => 'pago_unico',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Libros',
                'costo' => 300.00,
                'periodo' => 'pago_unico',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Inscripción',
                'costo' => 500.00,
                'periodo' => 'pago_unico',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Colegiatura',
                'costo' => 1200.00,
                'periodo' => 'mensual',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Pago Puntual',
                'costo' => 100.00,
                'periodo' => 'mensual',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Material y Mantenimiento',
                'costo' => 200.00,
                'periodo' => 'semestral',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Batería De Examenes',
                'costo' => 150.00,
                'periodo' => 'semestral',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Seguro Escolar',
                'costo' => 80.00,
                'periodo' => 'semestral',
                'nivel' => 'general',
                'modalidad' => 'general'
            ]
        ];

        foreach ($conceptos as $concepto) {
            Concepto::create($concepto);
        }
    }
}
