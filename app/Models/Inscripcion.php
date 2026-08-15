<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dato personal bajo la Ley 1581 de 2012.
 *
 * Regla del proyecto: el celular NUNCA se muestra en una vista publica.
 * Solo lo ve el coordinador desde el panel, que es quien necesita llamar.
 */
class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    protected $fillable = [
        'turno_id',
        'nombre',
        'celular',
        'autorizacion_datos',
        'autorizacion_en',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'autorizacion_datos' => 'boolean',
            'autorizacion_en' => 'datetime',
        ];
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Para listados donde no hace falta el numero completo.
     * No es anonimizacion: sirve para que el coordinador reconozca a quien
     * ya tiene apuntado sin exponer el numero entero en pantalla.
     */
    public function getCelularParcialAttribute(): string
    {
        $digitos = preg_replace('/\D/', '', (string) $this->celular);

        return strlen($digitos) <= 4
            ? str_repeat('•', strlen($digitos))
            : str_repeat('•', strlen($digitos) - 4).substr($digitos, -4);
    }

    public function getEstadoLegibleAttribute(): string
    {
        return match ($this->estado) {
            'anotado' => 'Anotado',
            'asistio' => 'Asistió',
            'no_asistio' => 'No asistió',
            default => 'Cancelado',
        };
    }
}
