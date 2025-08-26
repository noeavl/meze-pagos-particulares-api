<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Concepto extends Model
{
    protected $fillable = [
        'nombre',
        'costo',
        'periodo',
        'nivel',
        'modalidad'
    ];

    protected $casts = [
        'costo' => 'decimal:2'
    ];

    protected $attributes = [
        'nivel' => 'general',
        'modalidad' => 'general'
    ];

    public function conceptosNiveles(): HasMany
    {
        return $this->hasMany(Concepto::class, 'concepto_id');
    }
}
