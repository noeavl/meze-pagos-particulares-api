<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Adeudo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use App\Models\Concepto;
use App\Models\Estudiante;

class AdeudoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $q = $request->get('q');
            $estado = $request->get('estado');
            $estudiante_id = $request->get('estudiante_id');
            
            $query = Adeudo::with(['concepto', 'estudiante', 'estudiante.persona']);
            
            if($q) {
                $query->whereHas('estudiante.persona', function($subQuery) use ($q){
                    $subQuery->where('nombres', 'LIKE', "%{$q}%")
                        ->orWhere('apellido_paterno', 'LIKE', "%{$q}%")
                        ->orWhere('apellido_materno', 'LIKE', "%{$q}%");
                })->orWhereHas('concepto', function($subQuery) use ($q){
                    $subQuery->where('nombre', 'LIKE', "%{$q}%");
                });
            }

            if($estado) {
                $query->where('estado', $estado);
            }

            if($estudiante_id) {
                $query->where('estudiante_id', $estudiante_id);
            }

            $adeudos = $query->orderBy('fecha_vencimiento','desc')->orderByDesc('id')->get();
           
            return response()->json([
                'success' => true,
                'data' => $adeudos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener adeudos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'concepto_id' => 'required|exists:conceptos,id',
                'estudiante_id' => 'required|exists:estudiantes,id',
                'estado' => 'required|in:pendiente,pagado,vencido',
                'pendiente' => 'required|numeric|min:0',
                'pagado' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $adeudo = Adeudo::create([
                'concepto_id' => $request->concepto_id,
                'estudiante_id' => $request->estudiante_id,
                'estado' => $request->estado,
                'pendiente' => $request->pendiente,
                'pagado' => $request->pagado,
                'total' => $request->total,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_vencimiento' => $request->fecha_vencimiento
            ]);

            $adeudo->load(['concepto', 'estudiante', 'estudiante.persona']);

            return response()->json([
                'success' => true,
                'message' => 'Adeudo creado exitosamente',
                'data' => $adeudo
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear adeudo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMassiveDebts(Request $request){
        $validator = Validator::make($request->all(),[
            'fecha_inicio' => 'required|date',
            'fecha_vencimiento'=>'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>'Errores de validación',
                'errors'=>$validator->errors()
            ],422);
        }

        $estudiantes = Estudiante::where('estado',true)->get();
        $conceptos = Concepto::all();

            foreach($estudiantes as $e){
                foreach($conceptos as $c){
                    if( ($c->nivel == 'general' || $c->nivel == $e->nivel) && ($c->modalidad == $e->modalidad || $c->modalidad == 'general')){
                        if($c->periodo == 'semestral'){
                            $adeudoExistente = Adeudo::where([
                                'estudiante_id' => $e->id,
                                'concepto_id' => $c->id,
                                'fecha_inicio' => $request->fecha_inicio,
                                'fecha_vencimiento' => $request->fecha_vencimiento,
                            ])->exists();
                            if(!$adeudoExistente){
                                Adeudo::create([
                                    'estudiante_id' => $e->id,
                                    'concepto_id' => $c->id,
                                    'total'=> $c->costo,
                                    'fecha_inicio' => $request->fechaInicio,
                                    'fecha_vencimiento' => $request->fecha_vencimiento,
                                ]);
                            }
                        }
                    }
                }
            }


    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $adeudo = Adeudo::with(['concepto', 'estudiante', 'estudiante.persona', 'pagos'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $adeudo
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adeudo no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener adeudo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $adeudo = Adeudo::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'concepto_id' => 'required|exists:conceptos,id',
                'estudiante_id' => 'required|exists:estudiantes,id',
                'estado' => 'required|in:pendiente,pagado,vencido',
                'pendiente' => 'required|numeric|min:0',
                'pagado' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_vencimiento' => 'required|date|after_or_equal:fecha_inicio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $adeudo->update([
                'concepto_id' => $request->concepto_id,
                'estudiante_id' => $request->estudiante_id,
                'estado' => $request->estado,
                'pendiente' => $request->pendiente,
                'pagado' => $request->pagado,
                'total' => $request->total,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_vencimiento' => $request->fecha_vencimiento
            ]);

            $adeudo->load(['concepto', 'estudiante', 'estudiante.persona']);

            return response()->json([
                'success' => true,
                'message' => 'Adeudo actualizado exitosamente',
                'data' => $adeudo
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Adeudo no encontrado'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar adeudo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}