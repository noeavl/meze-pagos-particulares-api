<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\ConceptoController;
use App\Http\Controllers\Api\AdeudoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PagoController;

Route::prefix('v1')->group(function(){
    // Authentication routes (no middleware required)
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        // Legacy user route
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // User CRUD(NO DELETE) routes
        Route::get('users/search/{q?}',[UserController::class,'index']);
          Route::get('users',[UserController::class,'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::where(['user' => '[0-9]+'])->group(function(){
            Route::get('users/{user}', [UserController::class, 'show']); 
            Route::put('users/{user}', [UserController::class, 'update']);
        });

        // Estudiante CRUD(NO DELETE) routes
        Route::get('estudiantes/search/{q?}',[EstudianteController::class,'index']);
        Route::get('estudiantes',[EstudianteController::class,'index']);
        Route::post('estudiantes', [EstudianteController::class, 'store']);
        Route::where(['estudiante' => '[0-9]+'])->group(function(){
            Route::get('estudiantes/{estudiante}', [EstudianteController::class, 'show']); 
            Route::put('estudiantes/{estudiante}', [EstudianteController::class, 'update']);
        });

        // Concepto CRUD(NO DELETE) routes
        Route::get('conceptos/search/{q?}',[ConceptoController::class,'index']);
        Route::get('conceptos',[ConceptoController::class,'index']);
        Route::post('conceptos', [ConceptoController::class, 'store']);
        Route::where(['concepto' => '[0-9]+'])->group(function(){
            Route::get('conceptos/{concepto}', [ConceptoController::class, 'show']);
            Route::put('conceptos/{concepto}', [ConceptoController::class, 'update']);
        });
      
        // Adeudo CRUD(NO DELETE) routes
        Route::get('adeudos/search/{q?}', [AdeudoController::class, 'index']);
        Route::get('adeudos', [AdeudoController::class, 'index']); 
        Route::post('adeudos', [AdeudoController::class, 'store']);
        Route::where(['adeudo' => '[0-9]+'])->group(function(){
            Route::get('adeudos/{adeudo}', [AdeudoController::class, 'show']);
            Route::put('adeudos/{adeudo}', [AdeudoController::class, 'update']);
        });

        // Pago CRUD routes
        Route::get('pagos/search/{q?}', [PagoController::class, 'search']);
        Route::get('pagos', [PagoController::class, 'index']);
        Route::post('pagos', [PagoController::class, 'store']);
        Route::where(['pago' => '[0-9]+'])->group(function(){
            Route::get('pagos/{pago}', [PagoController::class, 'show']);
        });
    });
});