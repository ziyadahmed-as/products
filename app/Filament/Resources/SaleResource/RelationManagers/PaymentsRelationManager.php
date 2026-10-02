<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
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
                Tables\Columns\TextColumn::make('reference'),
                Tables\Columns\TextColumn::make('date')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record) {
                        $sale = $record->sale;
                        $totalPaid = $sale->payments()->sum('amount');
                        $sale->paid_amount = $totalPaid;
                        if ($totalPaid >= $sale->total) {
                            $sale->payment_status = 'paid';
                        } elseif ($totalPaid > 0) {
                            $sale->payment_status = 'partial';
                        } else {
                            $sale->payment_status = 'pending';
                        }
                        $sale->save();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($record) {
                        $sale = $record->sale;
                        $totalPaid = $sale->payments()->sum('amount');
                        $sale->paid_amount = $totalPaid;
                        if ($totalPaid >= $sale->total) {
                            $sale->payment_status = 'paid';
                        } elseif ($totalPaid > 0) {
                            $sale->payment_status = 'partial';
                        } else {
                            $sale->payment_status = 'pending';
                        }
                        $sale->save();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        $sale = $record->sale;
                        $totalPaid = $sale->payments()->sum('amount');
                        $sale->paid_amount = $totalPaid;
                        if ($totalPaid >= $sale->total) {
                            $sale->payment_status = 'paid';
                        } elseif ($totalPaid > 0) {
                            $sale->payment_status = 'partial';
                        } else {
                            $sale->payment_status = 'pending';
                        }
                        $sale->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
