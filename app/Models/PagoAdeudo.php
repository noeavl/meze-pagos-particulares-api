<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoAdeudo extends Model
{
    protected $table = 'pagos_adeudos';
    
    protected $fillable = [
        'pago_id',
        'adeudo_id'
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function adeudo(): BelongsTo
    {
        return $this->belongsTo(Adeudo::class);
    }
}
