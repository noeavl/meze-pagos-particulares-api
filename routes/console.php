<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\GenerarAdeudosEstudiantes;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');




Schedule::call(function(){
    $year = date('Y');
    $rangosFechas = [
            1 => [
                'inicio' => $year . '-08-01',
                'fin' => $year.'-09-15'
            ],
            2 => [
                'inicio' => ($year + 1) .'-02-01', 
                'fin' => ($year + 1).'-02-28'
            ]
        ];
    
    GenerarAdeudosEstudiantes::dispatch($rangosFechas);
})->everyMinute();




