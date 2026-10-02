<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\Select::make('type')
                        ->options([
                            'online'   => 'Online Order',
                            'pos'      => 'Point of Sale',
                            'direct'   => 'Direct Sale',
                        ])
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'    => 'Pending',
                            'confirmed'  => 'Confirmed',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'completed'  => 'Completed',
                            'cancelled'  => 'Cancelled',
                        ])
                        ->required()
                        ->default('pending'),
                    Forms\Components\Select::make('payment_status')
                        ->options([
                            'pending' => 'Pending',
                            'partial' => 'Partial',
                            'paid'    => 'Paid',
                            'refunded'=> 'Refunded',
                        ])
                        ->required()
                        ->default('pending'),
                ])->columns(2),

            Forms\Components\Section::make('Customer & Location')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->maxLength(255),
                    Forms\Components\Select::make('user_id')
                        ->label('Processed By')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Fulfillment Location')
                        ->relationship('storageLocation', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])->columns(3),

            Forms\Components\Section::make('Financials')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->required()->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('discount')
                        ->required()->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('tax')
                        ->required()->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('total')
                        ->required()->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('paid_amount')
                        ->required()->numeric()->prefix('$')->default(0),
                ])->columns(5),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'info',
                        'pos'    => 'success',
                        'direct' => 'warning',
                        default  => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'gray',
                        'confirmed'  => 'info',
                        'processing' => 'warning',
                        'shipped'    => 'primary',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'     => 'success',
                        'partial'  => 'warning',
                        'pending'  => 'gray',
                        'refunded' => 'danger',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending', 'confirmed' => 'Confirmed',
                        'processing' => 'Processing', 'shipped' => 'Shipped',
                        'completed' => 'Completed', 'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options([
                        'pending' => 'Pending', 'partial' => 'Partial',
                        'paid' => 'Paid', 'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'online' => 'Online', 'pos' => 'POS', 'direct' => 'Direct',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit'   => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
