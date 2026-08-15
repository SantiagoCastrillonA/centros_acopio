<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'items';

    protected $fillable = [
        'nombre',
        'unidad',
        'categoria',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Emoji de la categoria. En un celular, a pleno sol y con prisa,
     * la forma se reconoce antes que la palabra.
     */
    public function getEmojiAttribute(): string
    {
        return static::emojiDeCategoria($this->categoria);
    }

    public static function emojiDeCategoria(?string $categoria): string
    {
        return match ($categoria) {
            'alimento' => '🍚',
            'agua' => '💧',
            'higiene' => '🧼',
            'habitat' => '🏕️',
            'salud' => '🩺',
            'bebe' => '🍼',
            'herramienta' => '🔦',
            default => '📦',
        };
    }

    public function necesidades(): HasMany
    {
        return $this->hasMany(Necesidad::class);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
