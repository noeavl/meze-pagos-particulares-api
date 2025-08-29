<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\GenerarAdeudosEstudiantes;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Jobs\ActualizarEstadosAdeudosVencidos;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// // Schedule First Semestre
// Schedule::call(function(){
//     $year = date('Y');
//     GenerarAdeudosEstudiantes::dispatch($year .'-08-15');
// })->yearlyOn(8, 1, '00:00');

// // Schedule Second Semestre
// Schedule::call(function(){
//     $year = date('Y');
//     GenerarAdeudosEstudiantes::dispatch($year  .'-02-28');
// })->yearlyOn(2, 1, '00:00');


Schedule::call(function(){
    $year = date('Y');
    GenerarAdeudosEstudiantes::dispatch(($year ).'-08-01',($year  ) .'-08-15',$year);
})->yearly();

Schedule::call(function(){
    $year = date('Y');
    GenerarAdeudosEstudiantes::dispatch(($year + 1).'-02-01',($year + 1) .'-02-28', $year);
})->yearly();

// Schedule update estados vencidos.
Schedule::call(function(){
    ActualizarEstadosAdeudosVencidos::dispatch();
})->everyMinute();










