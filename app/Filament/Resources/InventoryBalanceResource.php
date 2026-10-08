<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryBalanceResource\Pages;
use App\Models\InventoryBalance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryBalanceResource extends Resource
{
    protected static ?string $model = InventoryBalance::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Stock Levels';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';



    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Balance Entry')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Storage Location')
                        ->relationship('storageLocation', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('quantity')
                        ->required()
                        ->numeric()->step('any')
                        ->minValue(0)
                        ->default(0),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('product.type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manufactured_product' => 'success',
                        'resale_product'       => 'info',
                        'raw_material'         => 'gray',
                        'consumable'           => 'warning',
                        default                => 'gray',
                    }),
                Tables\Columns\TextColumn::make('storageLocation.name')
                    ->label('Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()->step('any')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : ($state < 10 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('storageLocation')
                    ->relationship('storageLocation', 'name')
                    ->label('Location'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventoryBalances::route('/'),
            'create' => Pages\CreateInventoryBalance::route('/create'),
            'edit'   => Pages\EditInventoryBalance::route('/{record}/edit'),
        ];
    }
}
