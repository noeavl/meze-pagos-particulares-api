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
                'costo' => 250.00,
                'periodo' => 'pago_unico',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Credencial',
                'costo' => 70.00,
                'periodo' => 'pago_unico',
                'nivel' => 'general',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Libros',
                'costo' => 1400.00,
                'periodo' => 'pago_unico',
                'nivel' => 'preescolar',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Libros',
                'costo' => 1600.00,
                'periodo' => 'pago_unico',
                'nivel' => 'primaria',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Libros',
                'costo' => 1600.00,
                'periodo' => 'pago_unico',
                'nivel' => 'secundaria',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Material y Mantenimiento',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'preescolar',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Material y Mantenimiento',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'primaria',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Material y Mantenimiento',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'secundaria',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Batería De Examenes',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'bachillerato',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Batería De Examenes',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'primaria',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Batería De Examenes',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'secundaria',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Batería De Examenes',
                'costo' => 325.00,
                'periodo' => 'semestral',
                'nivel' => 'bachillerato',
                'modalidad' => 'general'
            ],
            [
                'nombre' => 'Seguro Escolar',
                'costo' => 250.00,
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
