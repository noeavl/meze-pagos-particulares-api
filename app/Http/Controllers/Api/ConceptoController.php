<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concepto;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class ConceptoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $q = $request->get('q');
            
            if($q){
                $query = Concepto::query();
                $query->where(function($subQuery) use ($q){
                    $subQuery->where('nombre', 'LIKE', "%{$q}%")
                         ->orWhere('periodo', 'LIKE', "%{$q}%")
                         ->orWhere('nivel', 'LIKE', "%{$q}%")
                         ->orWhere('modalidad', 'LIKE', "%{$q}%");
                });

                $conceptos = $query->orderByDesc('id')->get();
            }else {
                $conceptos = Concepto::orderByDesc('id')->get();
            }
           
            return response()->json([
                'success' => true,
                'data' => $conceptos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener conceptos',
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
                'nombre' => 'required|string|max:255',
                'costo' => 'required|numeric|min:0',
                'periodo' => 'required|in:pago_unico,mensual,semestral',
                'nivel' => 'required|in:general,preescolar,primaria,secundaria,bachillerato,bachillerato_sabatino',
                'modalidad' => 'required|in:general,presencial,en_linea'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $concepto = Concepto::create([
                'nombre' => $request->nombre,
                'costo' => $request->costo,
                'periodo' => $request->periodo,
                'nivel' => $request->nivel,
                'modalidad' => $request->modalidad
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Concepto creado exitosamente',
                'data' => $concepto
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
                'message' => 'Error al crear concepto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $concepto = Concepto::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $concepto
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Concepto no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener concepto',
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
            $concepto = Concepto::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'costo' => 'required|numeric|min:0',
                'periodo' => 'required|in:pago_unico,mensual,semestral',
                'nivel' => 'required|in:general,preescolar,primaria,secundaria,bachillerato,bachillerato_sabatino',
                'modalidad' => 'required|in:general,presencial,en_linea'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $concepto->update([
                'nombre' => $request->nombre,
                'costo' => $request->costo,
                'periodo' => $request->periodo,
                'nivel' => $request->nivel,
                'modalidad' => $request->modalidad
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Concepto actualizado exitosamente',
                'data' => $concepto
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Concepto no encontrado'
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
                'message' => 'Error al actualizar concepto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $concepto = Concepto::findOrFail($id);
            $concepto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Concepto eliminado exitosamente'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Concepto no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar concepto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
