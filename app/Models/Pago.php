<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'monto',
        'metodo_pago'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function adeudos(): BelongsToMany
    {
        return $this->belongsToMany(Adeudo::class, 'pagos_adeudos', 'pago_id', 'adeudo_id')
                    ->withTimestamps();
    }
}
