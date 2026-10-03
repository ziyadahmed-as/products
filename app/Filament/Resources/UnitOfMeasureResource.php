<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitOfMeasureResource\Pages;
use App\Models\UnitOfMeasure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnitOfMeasureResource extends Resource
{
    protected static ?string $model = UnitOfMeasure::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?string $navigationLabel = 'Units of Measure';
    protected static ?int $navigationSort = 3;


    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Unit Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20)
                        ->label('Abbreviation / Code'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUnitOfMeasures::route('/'),
            'create' => Pages\CreateUnitOfMeasure::route('/create'),
            'edit'   => Pages\EditUnitOfMeasure::route('/{record}/edit'),
        ];
    }
}
