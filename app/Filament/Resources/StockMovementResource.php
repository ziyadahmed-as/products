<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockMovementResource\Pages;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Movement History';
    protected static ?int $navigationSort = 5;

    // Read-only audit log — no create/edit needed

    protected static bool $canCreate = false;

    public static function canCreate(): bool { return false; }


    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Movement Record')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()->preload()->disabled(),
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Storage Location')
                        ->relationship('storageLocation', 'name')
                        ->searchable()->preload()->disabled(),
                    Forms\Components\TextInput::make('type')->disabled(),
                    Forms\Components\TextInput::make('quantity')->numeric()->disabled(),
                    Forms\Components\TextInput::make('unit_cost')->numeric()->prefix('$')->disabled(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('storageLocation.name')
                    ->label('Location')->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in'  => 'success',
                        'out' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('unit_cost')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Source')->badge()->color('info'),
                Tables\Columns\TextColumn::make('user.name')->label('By')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['in' => 'In (Receipt)', 'out' => 'Out (Issue)']),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
        ];
    }
}
