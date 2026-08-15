<?php

namespace App\Filament\Resources\Items\Tables;

use App\Models\Centro;
use App\Models\Necesidad;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ItemsTable
{
    private const CATEGORIAS = [
        'agua' => '💧 Agua',
        'alimento' => '🍚 Alimento',
        'habitat' => '🏕️ Hábitat y descanso',
        'higiene' => '🧼 Higiene',
        'bebe' => '🍼 Bebés y niños',
        'salud' => '🩺 Salud',
        'herramienta' => '🔦 Herramienta y logística',
        'otro' => '📦 Otro',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('categoria')
                    ->label('Categoría')
                    ->getTitleFromRecordUsing(fn ($record) => self::CATEGORIAS[$record->categoria] ?? '📦 Otro')
                    ->collapsible(),
            ])
            ->defaultGroup('categoria')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Insumo')
                    ->formatStateUsing(fn ($state, $record) => $record->emoji.' '.$state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unidad')
                    ->label('Unidad')
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('necesidades_count')
                    ->label('Centros que lo piden')
                    ->counts('necesidades')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                IconColumn::make('activo')
                    ->label('Disponible')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->options(self::CATEGORIAS),

                TernaryFilter::make('activo')
                    ->label('Disponible'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                self::asignarACentro(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre');
    }

    /**
     * Marcar varios insumos y publicarlos de una vez en un centro.
     *
     * Es el camino que un coordinador recorre al abrir un punto: llega con
     * una lista de lo que necesita, no con un insumo a la vez.
     */
    private static function asignarACentro(): BulkAction
    {
        return BulkAction::make('asignar')
            ->label('Publicar en un centro')
            ->icon(Heroicon::OutlinedPlusCircle)
            ->color('success')
            ->modalHeading('Publicar los insumos marcados en un centro')
            ->modalSubmitActionLabel('Publicar')
            ->schema([
                Select::make('centro_id')
                    ->label('Centro')
                    ->options(fn () => Centro::orderBy('nombre')->pluck('nombre', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('prioridad')
                    ->label('Prioridad')
                    ->options([
                        'alta' => '🚨 Urgente',
                        'media' => '⚠️ Necesario',
                        'baja' => '🕗 Cuando se pueda',
                    ])
                    ->default('media')
                    ->required(),

                TextInput::make('cantidad_requerida')
                    ->label('Cantidad requerida')
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(4294967295)
                    ->default(0)
                    ->helperText('La misma para todos los insumos marcados. Puede dejarla en 0 y ajustarla después desde Actualización rápida.'),
            ])
            ->action(function (Collection $records, array $data) {
                $centro = Centro::findOrFail($data['centro_id']);

                // Los que ya estaban publicados en ese centro no se tocan:
                // sobrescribirlos borraria lo que el coordinador ya recibio.
                $yaEstaban = Necesidad::where('centro_id', $centro->id)
                    ->whereIn('item_id', $records->pluck('id'))
                    ->pluck('item_id')
                    ->all();

                $nuevos = $records->reject(fn ($item) => in_array($item->id, $yaEstaban, true));

                foreach ($nuevos as $item) {
                    Necesidad::create([
                        'centro_id' => $centro->id,
                        'item_id' => $item->id,
                        'cantidad_requerida' => (int) ($data['cantidad_requerida'] ?? 0),
                        'cantidad_cubierta' => 0,
                        'prioridad' => $data['prioridad'],
                    ]);
                }

                Notification::make()
                    ->title($nuevos->count().' '.($nuevos->count() === 1 ? 'insumo publicado' : 'insumos publicados').' en '.$centro->nombre)
                    ->body(count($yaEstaban) > 0
                        ? count($yaEstaban).' ya estaban publicados y se dejaron como estaban.'
                        : 'Ajuste las cantidades desde Actualización rápida.')
                    ->success()
                    ->actions([
                        Action::make('rapido')
                            ->label('Ajustar cantidades')
                            ->url(route('rapido', $centro))
                            ->button(),
                    ])
                    ->persistent()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
