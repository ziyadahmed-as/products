<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleReturnResource\Pages;
use App\Models\SaleReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SaleReturnResource extends Resource
{
    protected static ?string $model = SaleReturn::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Returns & Refunds';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Return Details')
                ->schema([
                    Forms\Components\Select::make('sale_id')
                        ->label('Original Sale')
                        ->relationship('sale', 'reference')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('user_id')
                        ->label('Processed By')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'   => 'Pending',
                            'approved'  => 'Approved',
                            'completed' => 'Completed',
                            'rejected'  => 'Rejected',
                        ])
                        ->required()
                        ->default('pending'),
                    Forms\Components\TextInput::make('refund_amount')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                ])->columns(2),
            Forms\Components\Section::make('Reason')
                ->schema([
                    Forms\Components\Textarea::make('reason')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sale.reference')
                    ->label('Sale Reference')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Processed By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'gray',
                        'approved'  => 'info',
                        'completed' => 'success',
                        'rejected'  => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending', 'approved' => 'Approved',
                        'completed' => 'Completed', 'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSaleReturns::route('/'),
            'create' => Pages\CreateSaleReturn::route('/create'),
            'edit'   => Pages\EditSaleReturn::route('/{record}/edit'),
        ];
    }
}
