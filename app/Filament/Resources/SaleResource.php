<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\Product;
use App\Models\StorageLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\SaleResource\RelationManagers;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference';

    /**
     * Sellers only see their own orders.
     * Managers and Admins see all orders.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Seller')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    /**
     * Helper: get branch-scoped product IDs for the current seller.
     */
    private static function getSellerProductIds(): array
    {
        $user = auth()->user();
        if ($user->hasRole('Seller')) {
            $branchIds = $user->branches()->pluck('branches.id');
            return Product::whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds))
                ->whereIn('type', ['resale_product', 'manufactured_product'])
                ->pluck('id')
                ->toArray();
        }
        // Non-sellers see all sellable products
        return Product::whereIn('type', ['resale_product', 'manufactured_product'])->pluck('id')->toArray();
    }

    /**
     * Helper: get storage location IDs for the current seller's branches.
     */
    private static function getSellerLocationIds(): array
    {
        $user = auth()->user();
        if ($user->hasRole('Seller')) {
            $branchIds = $user->branches()->pluck('branches.id');
            return StorageLocation::whereIn('branch_id', $branchIds)->pluck('id')->toArray();
        }
        return [];
    }

    public static function form(Form $form): Form
    {
        $isSeller = auth()->user()->hasRole('Seller');

        return $form->schema([
            Forms\Components\Section::make('Order Details')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default('ORD-' . strtoupper(substr(uniqid(), -6)))
                        ->maxLength(100),
                    Forms\Components\Select::make('type')
                        ->options([
                            'online'   => 'Online Order',
                            'pos'      => 'Point of Sale',
                            'direct'   => 'Direct Sale',
                        ])
                        ->required()
                        ->default('pos'),
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
                            'pending'  => 'Pending',
                            'partial'  => 'Partial',
                            'paid'     => 'Paid',
                            'refunded' => 'Refunded',
                        ])
                        ->required()
                        ->default('pending'),
                ])->columns(2),

            Forms\Components\Section::make('Customer & Location')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->maxLength(255),

                    // Sellers are locked to themselves; managers can assign to others
                    Forms\Components\Select::make('user_id')
                        ->label('Processed By')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->default(auth()->id())
                        ->disabled($isSeller)
                        ->dehydrated(true),

                    // Sellers can only pick locations in their assigned branches
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Fulfillment Location')
                        ->options(function () use ($isSeller) {
                            if ($isSeller) {
                                $locationIds = static::getSellerLocationIds();
                                return StorageLocation::whereIn('id', $locationIds)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            }
                            return StorageLocation::where('is_active', true)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive(),
                ])->columns(3),

            Forms\Components\Section::make('Order Items')
                ->schema([
                    Forms\Components\Repeater::make('lines')
                        ->relationship()
                        ->schema([
                            // Sellers only see products from their assigned branch
                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->options(function () {
                                    $ids = static::getSellerProductIds();
                                    return Product::whereIn('id', $ids)->pluck('name', 'id');
                                })
                                ->required()
                                ->reactive()
                                ->searchable()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('unit_price', $product->selling_price ?? 0);
                                        $qty = $get('quantity') ?: 1;
                                        $set('total', $qty * ($product->selling_price ?? 0));
                                    }
                                }),
                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $set('total', $state * ($get('unit_price') ?? 0));
                                }),
                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $set('total', $state * ($get('quantity') ?? 1));
                                }),
                            Forms\Components\TextInput::make('total')
                                ->numeric()
                                ->required()
                                ->readOnly(),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data) {
                            $data['unit_cost'] = Product::find($data['product_id'])?->purchase_cost ?? 0;
                            return $data;
                        }),
                ]),

            Forms\Components\Section::make('Financials')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->required()->numeric()->prefix('$')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('discount')
                        ->required()->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('tax')
                        ->required()->numeric()->prefix('$')->default(0),
                    Forms\Components\TextInput::make('total')
                        ->required()->numeric()->prefix('$')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('paid_amount')
                        ->required()->numeric()->prefix('$')->default(0)->readOnly(),
                ])->columns(5),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()->sortable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()->placeholder('Guest'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Seller')
                    ->searchable()
                    ->toggleable()
                    ->visible(fn () => !auth()->user()->hasRole('Seller')),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'info', 'pos' => 'success', 'direct' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray', 'confirmed' => 'info', 'processing' => 'warning',
                        'shipped' => 'primary', 'completed' => 'success', 'cancelled' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success', 'partial' => 'warning', 'pending' => 'gray',
                        'refunded' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing',
                        'shipped' => 'Shipped', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options([
                        'pending' => 'Pending', 'partial' => 'Partial', 'paid' => 'Paid', 'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('print_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Sale $record) => route('invoice.show', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('confirm_and_deduct')
                    ->label('Confirm & Deduct Stock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Sale $record) => $record->status === 'pending')
                    ->action(function (Sale $record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->lines as $line) {
                                $balance = InventoryBalance::firstOrCreate([
                                    'product_id'         => $line->product_id,
                                    'storage_location_id' => $record->storage_location_id,
                                ], ['quantity' => 0]);

                                if ($balance->quantity < $line->quantity) {
                                    Notification::make()
                                        ->title('Insufficient Stock')
                                        ->body("Not enough stock for {$line->product->name}. Available: {$balance->quantity}")
                                        ->danger()
                                        ->send();
                                    throw new \Exception('Insufficient Stock');
                                }

                                $balance->decrement('quantity', $line->quantity);

                                StockMovement::create([
                                    'product_id'          => $line->product_id,
                                    'storage_location_id' => $record->storage_location_id,
                                    'type'                => 'out',
                                    'quantity'            => $line->quantity,
                                    'unit_cost'           => $line->unit_cost ?? 0,
                                    'user_id'             => auth()->id(),
                                    'reference_type'      => 'sale',
                                    'reference_id'        => $record->id,
                                ]);
                            }
                            $record->update(['status' => 'confirmed']);
                            Notification::make()->title('Order Confirmed and Stock Deducted')->success()->send();
                        });
                    }),
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
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
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
