<?php

namespace App\Filament\Resources\Turnos\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Quien se anoto a este turno.
 *
 * Es la unica pantalla del proyecto donde se ve un celular completo, y por
 * eso vive detras del panel. La vista publica nunca muestra estos datos.
 */
class InscripcionesRelationManager extends RelationManager
{
    protected static string $relationship = 'inscripciones';

    protected static ?string $title = 'Voluntarios anotados';

    protected static ?string $modelLabel = 'voluntario';

    protected static ?string $pluralModelLabel = 'voluntarios';

    public function form(Schema $schema): Schema
    {
        // Los voluntarios se anotan solos desde la vista publica. Aqui no se
        // crean a mano: hacerlo seria registrar a alguien sin su
        // autorizacion, que es justo lo que exige la Ley 1581.
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('celular')
                    ->label('Celular')
                    ->icon(Heroicon::OutlinedPhone)
                    ->copyable()
                    ->copyMessage('Celular copiado')
                    ->url(fn ($record) => 'tel:'.$record->celular),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->state(fn ($record) => $record->estado_legible)
                    ->color(fn ($record) => match ($record->estado) {
                        'asistio' => 'success',
                        'no_asistio' => 'danger',
                        'cancelado' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Se anotó')
                    ->since()
                    ->sortable(),

                TextColumn::make('autorizacion_en')
                    ->label('Autorizó sus datos')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'anotado' => 'Anotado',
                        'asistio' => 'Asistió',
                        'no_asistio' => 'No asistió',
                        'cancelado' => 'Cancelado',
                    ]),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('asistio')
                    ->label('Asistió')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn ($record) => $record->estado === 'anotado')
                    ->action(fn ($record) => $record->update(['estado' => 'asistio'])),

                Action::make('no_asistio')
                    ->label('No asistió')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn ($record) => $record->estado === 'anotado')
                    ->action(fn ($record) => $record->update(['estado' => 'no_asistio'])),

                DeleteAction::make()
                    ->label('Borrar datos')
                    ->modalHeading('Borrar los datos de este voluntario')
                    ->modalDescription('Se elimina su nombre y su celular. Úselo cuando la persona pida que borremos sus datos.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Borrar datos'),
                ]),
            ])
            ->defaultSort('created_at');
    }
}
