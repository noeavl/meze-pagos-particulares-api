<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Models\Adeudo;
use Carbon\Carbon;

class ActualizarEstadosAdeudosVencidos implements ShouldQueue
{
    use Queueable,SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adeudosVencidos = Adeudo::where('fecha_vencimiento','<',Carbon::now()->toDate())->get();

        foreach($adeudosVencidos as $av){
            $av->update([
                'estado'=>'vencido'
            ]);
        }
    }
}
