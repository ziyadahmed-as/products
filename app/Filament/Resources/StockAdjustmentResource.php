<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Models\StockAdjustment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Stock Adjustments';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Adjustment Details')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->searchable()->preload()->required(),
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Storage Location')
                        ->relationship('storageLocation', 'name')
                        ->searchable()->preload()->required(),
                    Forms\Components\Select::make('user_id')
                        ->label('Adjusted By')
                        ->relationship('user', 'name')
                        ->searchable()->preload(),
                    Forms\Components\Select::make('type')
                        ->options([
                            'addition'   => 'Addition (Positive)',
                            'deduction'  => 'Deduction (Negative)',
                            'correction' => 'Correction',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('quantity')
                        ->required()->numeric(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'  => 'Pending',
                            'approved' => 'Approved',
                            'rejected' => 'Rejected',
                        ])
                        ->required()->default('pending'),
                ])->columns(3),
            Forms\Components\Section::make('Reason')
                ->schema([
                    Forms\Components\Textarea::make('reason')
                        ->required()->rows(3)->columnSpanFull(),
                ]),
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
                        'addition'   => 'success',
                        'deduction'  => 'danger',
                        'correction' => 'warning',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('quantity')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')->label('By')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
                Tables\Filters\SelectFilter::make('type')
                    ->options(['addition' => 'Addition', 'deduction' => 'Deduction', 'correction' => 'Correction']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'edit'   => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}
