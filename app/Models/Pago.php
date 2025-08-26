<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pago extends Model
{
    protected $fillable = [
        'folio',
        'monto',
        'metodo_pago'
    ];

    protected $casts = [
        'monto' => 'decimal:2'
    ];

    public function adeudos(): BelongsToMany
    {
        return $this->belongsToMany(Adeudo::class, 'pagos_adeudos', 'pago_id', 'adeudo_id');
    }
}
