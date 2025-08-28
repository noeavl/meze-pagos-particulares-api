<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Adeudo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\PagoAdeudo;

class PagoAdeudoController extends Controller
{
    /**
     * Display a listing of payments with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $estudiante_id = $request->get('estudiante_id');

            if($estudiante_id){
                $query = PagoAdeudo::with(['pago', 'adeudo'])->whereHas('pago', function($subQuery) use ($estudiante_id){
                    $subQuery->where('estudiante_id', $estudiante_id);
                });
            }else{
                $query = PagoAdeudo::with(['pago', 'adeudo']);
            }

            $pagosAdeudos = $query->get();

            return response()->json([
                'success' => true,
                'data' => $pagosAdeudos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'estudiante_id' => 'exists:estudiantes,id',
                'adeudo_id' => 'exists:adeudos,id',
                'folio' => 'required|string|unique:pagos,folio',
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|in:efectivo,transferencia',
                'fecha' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $adeudo = Adeudo::where('id', $request->adeudo_id)->first();

            if (!$adeudo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontro el adeudo.'
                ], 404);
            }

            $pendiente = $adeudo->pendiente - $request->monto;
            if($adeudo->estado == 'pagado'){
                return response()->json([
                    'success' => false,
                    'message' => 'El adeudo ya se encuentra pagado.'
                ], 409);
            }

            $pagado = $adeudo->pagado + $request->monto;

            $estado = $pendiente <= 0 ? 'pagado' : 'pendiente';
            
            $adeudo->update([
                'pendiente' => $pendiente > 0 ? $pendiente : 0,
                'pagado' => $pagado,
                'estado' => $estado
            ]);

            // Create payment
            $pago = Pago::create([
                'estudiante_id' => $request->estudiante_id,
                'folio' => $request->folio,
                'monto' => $request->monto,
                'metodo_pago' => $request->metodo_pago
            ]);

            PagoAdeudo::create([
                'pago_id' => $pago->id,
                'adeudo_id' => $adeudo->id
            ]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment
     */
    public function show($id): JsonResponse
    {
        try {
            $pago = Pago::with(['adeudos.concepto', 'adeudos.estudiante.persona'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $pago
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pago no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search payments with multiple filters
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = Pago::with(['adeudos.concepto', 'adeudos.estudiante.persona']);

            // Search by folio
            if ($request->has('folio') && !empty($request->folio)) {
                $query->where('folio', 'like', '%' . $request->folio . '%');
            }

            // Search by exact amount
            if ($request->has('monto') && !empty($request->monto)) {
                $query->where('monto', $request->monto);
            }

            // Search by amount range
            if ($request->has('monto_desde') && !empty($request->monto_desde)) {
                $query->where('monto', '>=', $request->monto_desde);
            }

            if ($request->has('monto_hasta') && !empty($request->monto_hasta)) {
                $query->where('monto', '<=', $request->monto_hasta);
            }

            // Search by payment method
            if ($request->has('metodo_pago') && !empty($request->metodo_pago)) {
                $query->where('metodo_pago', $request->metodo_pago);
            }

            // Search by student name
            if ($request->has('estudiante') && !empty($request->estudiante)) {
                $query->whereHas('adeudos.estudiante.persona', function($q) use ($request) {
                    $q->where('nombres', 'like', '%' . $request->estudiante . '%')
                      ->orWhere('apellido_paterno', 'like', '%' . $request->estudiante . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $request->estudiante . '%');
                });
            }

            // Search by concept name
            if ($request->has('concepto') && !empty($request->concepto)) {
                $query->whereHas('adeudos.concepto', function($q) use ($request) {
                    $q->where('nombre', 'like', '%' . $request->concepto . '%');
                });
            }

            // Search by date range
            if ($request->has('fecha_desde') && !empty($request->fecha_desde)) {
                $query->whereDate('created_at', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta') && !empty($request->fecha_hasta)) {
                $query->whereDate('created_at', '<=', $request->fecha_hasta);
            }

            // General text search across multiple fields
            if ($request->has('q') && !empty($request->q)) {
                $searchTerm = $request->q;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('folio', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('adeudos.estudiante.persona', function($subQ) use ($searchTerm) {
                          $subQ->where('nombres', 'like', '%' . $searchTerm . '%')
                               ->orWhere('apellido_paterno', 'like', '%' . $searchTerm . '%')
                               ->orWhere('apellido_materno', 'like', '%' . $searchTerm . '%');
                      })
                      ->orWhereHas('adeudos.concepto', function($subQ) use ($searchTerm) {
                          $subQ->where('nombre', 'like', '%' . $searchTerm . '%');
                      });
                });
            }

            $perPage = $request->get('per_page', 15);
            $pagos = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $pagos
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar pagos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, int $id){
        $pagoAdeudo = PagoAdeudo::where('id', $id)->first();

        if(!$pagoAdeudo){
            return response()->json([
                'success'=>false,
                'message'=>'Pago no encontrado'
            ],404);
        }

        $validator = Validator::make($request->all(),[
           'folio' => 'required|string|unique:pagos,folio',
           'monto' => 'required|numeric|min:0.01',
           'metodo_pago' => 'required|in:efectivo,transferencia',
           'fecha' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>'Errores de validación',
                'errors' => $validator->errors()
            ],422);
        }

        $pagoAdeudo->update([
            'folio' => $request->folio,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'fecha' => $request->fecha
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Pago actualizado correctamente'
        ],200);
    }
}
