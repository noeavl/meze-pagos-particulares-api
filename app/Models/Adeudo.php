<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Adeudo extends Model
{
    use HasFactory;
    protected $fillable = [
        'concepto_id',
        'estudiante_id',
        'estado',
        'pendiente',
        'pagado',
        'total',
        'fecha_inicio',
        'fecha_vencimiento'
    ];

    protected $casts = [
        'pendiente' => 'decimal:2',
        'pagado' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date'
    ];

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Concepto::class, 'concepto_id');
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function pagos(): BelongsToMany
    {
        return $this->belongsToMany(Pago::class, 'pagos_adeudos', 'adeudo_id', 'pago_id');
    }
}
