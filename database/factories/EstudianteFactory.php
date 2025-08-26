<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estudiante>
 */
class EstudianteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'persona_id' => \App\Models\Persona::factory(),
            'nivel' => fake()->randomElement(['preescolar', 'primaria', 'secundaria', 'bachillerato', 'bachillerato_sabatino']),
            'grado' => fake()->randomElement(['1', '2', '3', '4', '5', '6']),
            'modalidad' => fake()->randomElement(['presencial', 'en_linea']),
        ];
    }
}
