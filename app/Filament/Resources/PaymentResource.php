<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Payments';
    protected static ?int $navigationSort = 3;


    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payment Details')
                ->schema([
                    Forms\Components\Select::make('sale_id')
                        ->label('Sale Order')
                        ->relationship('sale', 'reference')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->required()
                        ->numeric()
                        ->prefix('Br '),
                    Forms\Components\Select::make('method')
                        ->options([
                            'cash'          => 'Cash',
                            'credit_card'   => 'Credit Card',
                            'bank_transfer' => 'Bank Transfer',
                            'online'        => 'Online Payment',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('reference')
                        ->maxLength(100),
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->default(today()),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.reference')
                    ->label('Sale')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('ETB')
                    ->sortable(),
                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash'          => 'success',
                        'credit_card'   => 'info',
                        'bank_transfer' => 'warning',
                        'online'        => 'primary',
                        default         => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reference')
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'cash' => 'Cash', 'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer', 'online' => 'Online',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
