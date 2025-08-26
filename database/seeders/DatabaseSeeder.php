<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ConceptoSeeder::class,
            UserSeeder::class,
            EstudianteSeeder::class,
            AdeudoSeeder::class,
        ]);
    }
}
