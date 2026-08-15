<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    protected $table = 'turnos';

    protected $fillable = [
        'centro_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tipo_tarea',
        'cupos',
        'nota',
        'abierto',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'cupos' => 'integer',
            'abierto' => 'boolean',
        ];
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centro::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(Inscripcion::class);
    }

    /**
     * Inscripciones que ocupan cupo. Una cancelada libera el suyo.
     */
    public function inscripcionesActivas(): HasMany
    {
        return $this->inscripciones()->whereIn('estado', ['anotado', 'asistio']);
    }

    /**
     * Turnos que el publico puede ver: abiertos y de hoy en adelante.
     */
    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('abierto', true)
            ->whereDate('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio');
    }

    public function getCuposTomadosAttribute(): int
    {
        return $this->inscripcionesActivas()->count();
    }

    public function getCuposLibresAttribute(): int
    {
        return max(0, $this->cupos - $this->cupos_tomados);
    }

    public function getLlenoAttribute(): bool
    {
        return $this->cupos_libres === 0;
    }

    public function getPasoAttribute(): bool
    {
        return $this->fecha->isBefore(now()->startOfDay());
    }

    public function getAdmiteInscripcionesAttribute(): bool
    {
        return $this->abierto && ! $this->lleno && ! $this->paso;
    }

    public function getEmojiAttribute(): string
    {
        return match ($this->tipo_tarea) {
            'clasificar' => '📦',
            'cargar' => '💪',
            'cocinar' => '🍲',
            'atender' => '🤝',
            'aseo' => '🧹',
            'inventario' => '📋',
            default => '🙌',
        };
    }

    public function getTareaAttribute(): string
    {
        return match ($this->tipo_tarea) {
            'clasificar' => 'Clasificar donaciones',
            'cargar' => 'Cargar y descargar',
            'cocinar' => 'Cocinar',
            'atender' => 'Atender a las familias',
            'aseo' => 'Aseo del centro',
            'inventario' => 'Llevar el inventario',
            default => 'Ayuda general',
        };
    }

    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5).' a '.substr($this->hora_fin, 0, 5);
    }
}
