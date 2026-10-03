<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockReceiptResource\Pages;
use App\Models\StockReceipt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockReceiptResource extends Resource
{
    protected static ?string $model = StockReceipt::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?int $navigationSort = 2;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'reference';


    public static function canAccess(): bool
    {
        return !auth()->user()->hasRole('Seller');
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Receipt Details')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default('RCT-' . strtoupper(substr(uniqid(), -6)))
                        ->maxLength(100),
                    Forms\Components\DatePicker::make('date')
                        ->required()
                        ->default(today()),
                    Forms\Components\Select::make('purchase_order_id')
                        ->relationship('purchaseOrder', 'reference')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->label('Purchase Order (Optional)'),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Received By'),
                ])->columns(2),

            Forms\Components\Section::make('Storage & Costs')
                ->schema([
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Storage Location')
                        ->relationship('storageLocation', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\TextInput::make('total_cost')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                ])->columns(2),

            Forms\Components\Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->collapsed(),
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
                Tables\Columns\TextColumn::make('purchaseOrder.reference')
                    ->label('PO Reference')
                    ->placeholder('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('storageLocation.name')
                    ->label('Storage Location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Received By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockReceipts::route('/'),
            'create' => Pages\CreateStockReceipt::route('/create'),
            'edit'   => Pages\EditStockReceipt::route('/{record}/edit'),
        ];
    }
}
