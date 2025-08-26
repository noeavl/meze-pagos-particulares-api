<?php

namespace App\Jobs;

use App\Models\Adeudo;
use App\Models\Concepto;
use App\Models\Estudiante;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerarAdeudosEstudiantes implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(
        public array $rangosFechas,
        public ?string $nivel = null,
        public ?string $modalidad = null
    ) {}

    public function handle(): void
    {
        $conceptos = Concepto::whereIn('nombre', [
            'Credencial',
            'Seguro escolar', 
            'Batería de exámenes',
            'Material y Mantenimiento'
        ])->get();
        
        $estudiantes = Estudiante::query()
            ->when($this->nivel, fn($q) => $q->where('nivel', $this->nivel))
            ->when($this->modalidad, fn($q) => $q->where('modalidad', $this->modalidad))
            ->get();

        foreach ($estudiantes as $estudiante) {
            foreach ($conceptos as $concepto) {
                foreach ($this->rangosFechas as $fechas) {
                    $adeudoExistente = Adeudo::where([
                        'estudiante_id' => $estudiante->id,
                        'concepto_id' => $concepto->id,
                        'fecha_inicio' => $fechas['inicio'],
                        'fecha_vencimiento' => $fechas['fin']
                    ])->exists();

                    if (!$adeudoExistente) {
                        Adeudo::create([
                            'estudiante_id' => $estudiante->id,
                            'concepto_id' => $concepto->id,
                            'estado' => 'pendiente',
                            'total' => $concepto->costo,
                            'pendiente' => $concepto->costo,
                            'pagado' => 0,
                            'fecha_inicio' => $fechas['inicio'],
                            'fecha_vencimiento' => $fechas['fin']
                        ]);
                    }
                }
            }
        }
    }
}
