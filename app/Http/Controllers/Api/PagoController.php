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

class PagoController extends Controller
{
    /**
     * Display a listing of payments with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            
            $pagos = Pago::with(['adeudos.concepto', 'adeudos.estudiante.persona'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $pagos
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
                'folio' => 'required|string|unique:pagos,folio',
                'monto' => 'required|numeric|min:0.01',
                'metodo_pago' => 'required|in:efectivo,transferencia',
                'adeudos' => 'required|array|min:1',
                'adeudos.*' => 'exists:adeudos,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get debts to be paid
            $adeudos = Adeudo::whereIn('id', $request->adeudos)->get();
            
            if ($adeudos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron adeudos válidos.'
                ], 400);
            }

            // Calculate total pending amount
            $totalPendiente = $adeudos->sum('pendiente');
            
            if ($request->monto > $totalPendiente) {
                return response()->json([
                    'success' => false,
                    'message' => "El monto excede el total pendiente ($totalPendiente)."
                ], 400);
            }

            // Create payment
            $pago = Pago::create([
                'folio' => $request->folio,
                'monto' => $request->monto,
                'metodo_pago' => $request->metodo_pago
            ]);

            // Distribute payment across debts
            $montoRestante = $request->monto;
            
            foreach ($adeudos as $adeudo) {
                if ($montoRestante <= 0) break;
                
                $montoAplicar = min($montoRestante, $adeudo->pendiente);
                
                // Associate payment with debt
                $pago->adeudos()->attach($adeudo->id);
                
                // Update debt amounts
                $nuevoPendiente = $adeudo->pendiente - $montoAplicar;
                $nuevoPagado = $adeudo->pagado + $montoAplicar;
                
                $adeudo->update([
                    'pendiente' => $nuevoPendiente,
                    'pagado' => $nuevoPagado,
                    'estado' => $nuevoPendiente <= 0 ? 'pagado' : 'pendiente'
                ]);
                
                $montoRestante -= $montoAplicar;
            }

            DB::commit();

            $pago->load(['adeudos.concepto', 'adeudos.estudiante.persona']);
            
            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'data' => $pago
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
}
