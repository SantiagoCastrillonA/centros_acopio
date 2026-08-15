<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Necesidad extends Model
{
    protected $table = 'necesidades';

    protected $fillable = [
        'centro_id',
        'item_id',
        'cantidad_requerida',
        'cantidad_cubierta',
        'prioridad',
        'nota',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_requerida' => 'integer',
            'cantidad_cubierta' => 'integer',
        ];
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centro::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Cuanto falta por conseguir. Nunca negativo.
     */
    public function getPendienteAttribute(): int
    {
        return max(0, $this->cantidad_requerida - $this->cantidad_cubierta);
    }

    /**
     * Porcentaje cubierto, tope 100.
     */
    public function getPorcentajeAttribute(): int
    {
        if ($this->cantidad_requerida <= 0) {
            return 100;
        }

        return (int) min(100, round($this->cantidad_cubierta * 100 / $this->cantidad_requerida));
    }

    public function getCubiertaAttribute(): bool
    {
        return $this->pendiente === 0;
    }
}
