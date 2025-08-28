<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class EstudianteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $q = $request->get('q');
            $modalidad = $request->get('modalidad');
            $nivel = $request->get('nivel');
            $grado = $request->get('grado');

            $query = Estudiante::query()->with('persona');

            // Apply specific filters if provided
            if ($modalidad) {
                $query->where('modalidad', $modalidad);
            }

            if ($nivel) {
                $query->where('nivel', $nivel);
            }

            if ($grado) {
                $query->where('grado', $grado);
            }

            // Apply search filter if provided
            if ($q) {
                $query->where(function($subQuery) use ($q) {
                    $subQuery->where('nivel', 'LIKE', "%{$q}%")
                         ->orWhere('grado', 'LIKE', "%{$q}%")
                         ->orWhere('modalidad','LIKE',"%{$q}%")
                         ->orWhereHas('persona', function($personaQuery) use ($q) {
                             $personaQuery->where('nombres', 'LIKE', "%{$q}%")
                                         ->orWhere('apellido_paterno', 'LIKE', "%{$q}%")
                                         ->orWhere('apellido_materno', 'LIKE', "%{$q}%");
                         });
                });
            }

            $estudiantes = $query->orderByDesc('id')->get();
           
            return response()->json([
                'success' => true,
                'data' => $estudiantes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes',
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
                'nombres' => 'required|string|max:255',
                'apellido_paterno' => 'required|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'nivel' => 'required|in:preescolar,primaria,secundaria,bachillerato,bachillerato_sabatino',
                'grado' => 'required|in:1,2,3,4,5,6',
                'modalidad'=>'required|in:presencial,en_linea'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // First create the persona
            $persona = Persona::create([
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
            ]);

            $estudiante = Estudiante::create([
                'persona_id' => $persona->id,
                'nivel' => $request->nivel,
                'grado' => $request->grado,
                'modalidad' => $request->modalidad
            ]);

            // Load the persona relationship for response
            $estudiante->load('persona');

            return response()->json([
                'success' => true,
                'message' => 'Estudiante creado exitosamente',
                'data' => $estudiante
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
                'message' => 'Error al crear estudiante',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $estudiante = Estudiante::with('persona')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $estudiante
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiante',
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
            $estudiante = Estudiante::with('persona')->findOrFail($id);

            $validator = Validator::make($request->all(), [
                    'nombres' => 'required|string|max:255',
                    'apellido_paterno' => 'required|string|max:255',
                    'apellido_materno' => 'nullable|string|max:255',
                    'nivel' => 'required|in:preescolar,primaria,secundaria,bachillerato,bachillerato_sabatino',
                    'grado' => 'required|in:1,2,3,4,5,6',
                    'modalidad'=>'required|in:presencial,en_linea'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Errores de validación',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Update persona data
                $estudiante->persona->update([
                    'nombres' => $request->nombres,
                    'apellido_paterno' => $request->apellido_paterno,
                    'apellido_materno' => $request->apellido_materno,
                ]);

                // Update estudiante data
                $estudiante->update([
                    'nivel' => $request->nivel,
                    'grado' => $request->grado,
                    'modalidad' => $request->modalidad
                ]);

                // Refresh the estudiante with persona data
                $estudiante->refresh();
                $estudiante->load('persona');

            return response()->json([
                'success' => true,
                'message' => 'Estudiante actualizado exitosamente',
                'data' => $estudiante
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estudiante',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
