<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManufacturingOrderResource\Pages;
use App\Models\ManufacturingOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ManufacturingOrderResource extends Resource
{
    protected static ?string $model = ManufacturingOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Manufacturing';
    protected static ?string $navigationLabel = 'Production Orders';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Reference')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default('MFG-' . strtoupper(substr(uniqid(), -6)))
                        ->maxLength(100),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft'       => 'Draft',
                            'planned'     => 'Planned',
                            'in_progress' => 'In Progress',
                            'completed'   => 'Completed',
                            'cancelled'   => 'Cancelled',
                        ])
                        ->required()
                        ->default('draft'),
                    Forms\Components\Select::make('user_id')
                        ->label('Production Manager')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload(),
                ])->columns(3),

            Forms\Components\Section::make('Product & Recipe')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Manufactured Product')
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('recipe_id')
                        ->label('Recipe / BOM')
                        ->relationship('recipe', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Output Storage Location')
                        ->relationship('storageLocation', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])->columns(3),

            Forms\Components\Section::make('Quantities & Dates')
                ->schema([
                    Forms\Components\TextInput::make('planned_quantity')
                        ->label('Planned Qty')
                        ->required()
                        ->numeric()
                        ->minValue(1),
                    Forms\Components\TextInput::make('good_quantity')
                        ->label('Good Qty')
                        ->required()
                        ->numeric()
                        ->default(0),
                    Forms\Components\TextInput::make('rejected_quantity')
                        ->label('Rejected Qty')
                        ->required()
                        ->numeric()
                        ->default(0),
                    Forms\Components\DatePicker::make('planned_date')
                        ->required()
                        ->default(today()),
                ])->columns(4),

            Forms\Components\Section::make('Costs')
                ->schema([
                    Forms\Components\TextInput::make('additional_costs')
                        ->label('Additional Costs')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\TextInput::make('total_cost')
                        ->label('Total Cost')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipe.name')
                    ->label('Recipe')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('planned_quantity')
                    ->label('Planned')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('good_quantity')
                    ->label('Good')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rejected_quantity')
                    ->label('Rejected')
                    ->numeric()
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'       => 'gray',
                        'planned'     => 'info',
                        'in_progress' => 'warning',
                        'completed'   => 'success',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('planned_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'       => 'Draft',
                        'planned'     => 'Planned',
                        'in_progress' => 'In Progress',
                        'completed'   => 'Completed',
                        'cancelled'   => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('planned_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListManufacturingOrders::route('/'),
            'create' => Pages\CreateManufacturingOrder::route('/create'),
            'edit'   => Pages\EditManufacturingOrder::route('/{record}/edit'),
        ];
    }
}
