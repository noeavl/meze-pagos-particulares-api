<?php

namespace App\Console\Commands;

use App\Jobs\GenerarAdeudosEstudiantes;
use Illuminate\Console\Command;

class GenerarAdeudosEscolares extends Command
{
    protected $signature = 'adeudos:generar
                           {--semestre=1 : Semestre (1 o 2)}
                           {--nivel= : Filtrar por nivel específico}
                           {--modalidad= : Filtrar por modalidad específica}';

    protected $description = 'Genera adeudos de conceptos escolares para todos los estudiantes';

    public function handle()
    {
        $semestre = $this->option('semestre');
        $nivel = $this->option('nivel');
        $modalidad = $this->option('modalidad');

        $rangosFechas = [
            1 => [
                'inicio' => '2024-08-01',
                'fin' => '2024-09-15'
            ],
            2 => [
                'inicio' => '2025-02-01', 
                'fin' => '2025-02-28'
            ]
        ];

        $rangoSeleccionado = [$rangosFechas[$semestre]];

        $this->info("Generando adeudos para semestre {$semestre}...");
        
        if ($nivel) {
            $this->info("Filtrando por nivel: {$nivel}");
        }
        
        if ($modalidad) {
            $this->info("Filtrando por modalidad: {$modalidad}");
        }

        GenerarAdeudosEstudiantes::dispatch($rangoSeleccionado, $nivel, $modalidad);

        $this->info('Job de generación de adeudos enviado a la cola.');
        $this->info('Usa: php artisan queue:work para procesarlo');
    }
}
