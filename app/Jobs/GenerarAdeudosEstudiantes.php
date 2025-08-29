<?php

namespace App\Jobs;

use App\Models\Adeudo;
use App\Models\Concepto;
use App\Models\Estudiante;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class GenerarAdeudosEstudiantes implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;

    public function __construct(public $fechaInicio,public $fechaFin, public $year) {}

    public function handle(): void
    {
        try {
            $estudiantes = Estudiante::where('estado',true)->get();
            $conceptos = Concepto::all();

            foreach($estudiantes as $e){
                foreach($conceptos as $c){
                    if( ($c->nivel == 'general' || $c->nivel == $e->nivel) && ($c->modalidad == $e->modalidad || $c->modalidad == 'general')){
                        if($c->periodo == 'semestral'){
                            $adeudoExistente = Adeudo::where([
                                'estudiante_id' => $e->id,
                                'concepto_id' => $c->id,
                                'fecha_inicio' => $this->fechaInicio,
                                'fecha_vencimiento' => $this->fechaFin,
                            ])->exists();
                            if(!$adeudoExistente){
                                Adeudo::create([
                                    'estudiante_id' => $e->id,
                                    'concepto_id' => $c->id,
                                    'total'=> $c->costo,
                                    'fecha_inicio' => $this->fechaInicio,
                                    'fecha_vencimiento' => $this->fechaFin,
                                ]);
                            }else if($c->periodo == 'pago_unico'){
                                Adeudo::create([
                                    'estudiante_id'=> $e->id,
                                    'concepto_id' => $c->id,
                                    'total' => $c->costo,
                                    'fecha_inicio' => $this->year . Carbon::now()->toDateString(),
                                    'fecha_vencimiento' => ($this->year + 1) . Carbon::now()->toDateString(),
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error al generar adeudos: ' . $e->getMessage());
        }
    }
}
