<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\Product;
use App\Models\StorageLocation;
use App\Models\InventoryBalance;
use App\Models\StockMovement;
use App\Services\SaleService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Orders';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'reference';

    // ─────────────────────────────────────────────────────────────────
    //  Query Scoping — role-based row visibility
    // ─────────────────────────────────────────────────────────────────

    /**
     * Sellers only see their own sales.
     * Managers see sales from their authorised branches.
     * Super Admin sees everything.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user->hasRole('Seller')) {
            // Sellers: only their own sales.
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('Manager')) {
            // Managers: sales from branches they manage.
            $branchIds = $user->authorizedBranchIds();
            $query->whereIn('branch_id', $branchIds);
        }
        // Super Admin: no restriction — sees all.

        return $query;
    }

    // ─────────────────────────────────────────────────────────────────
    //  Helpers — branch/product/location lists per logged-in user
    // ─────────────────────────────────────────────────────────────────

    /**
     * Authorised Branch objects for the current user.
     * Seller  → their single assigned branch.
     * Manager → all branches assigned to them.
     * SuperAdmin → all active branches.
     */
    private static function getAuthorizedBranches(): \Illuminate\Database\Eloquent\Collection
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            return Branch::where('is_active', true)->get();
        }

        return $user->branches()->where('is_active', true)->get();
    }

    /**
     * Branch-scoped product options for the order items repeater.
     * When a branch_id is available (from form state), limit to that branch's products.
     * Otherwise fall back to the user's full authorised set.
     */
    private static function getProductOptionsForBranch(?int $branchId): array
    {
        $user = auth()->user();

        if ($branchId) {
            // Show products that are assigned to the selected branch and are sellable.
            return Product::whereHas(
                'branches',
                fn ($q) => $q->where('branches.id', $branchId)
            )
                ->whereIn('type', ['resale_product', 'manufactured_product'])
                ->where('is_active', true)
                ->pluck('name', 'id')
                ->toArray();
        }

        // Fallback: all products visible to this user (used before a branch is selected).
        if ($user->hasRole('Super Admin')) {
            return Product::whereIn('type', ['resale_product', 'manufactured_product'])
                ->where('is_active', true)
                ->pluck('name', 'id')
                ->toArray();
        }

        $branchIds = $user->authorizedBranchIds();
        return Product::whereHas(
            'branches',
            fn ($q) => $q->whereIn('branches.id', $branchIds)
        )
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Storage location options for a given branch.
     */
    private static function getLocationOptionsForBranch(?int $branchId): array
    {
        if (!$branchId) {
            return [];
        }

        return StorageLocation::where('branch_id', $branchId)
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get branch-scoped available quantity for a product.
     */
    private static function getAvailableQty(int $productId, ?int $branchId): float
    {
        if (!$branchId) {
            return 0;
        }

        $locationIds = StorageLocation::where('branch_id', $branchId)
            ->where('is_active', true)
            ->pluck('id');

        return (float) InventoryBalance::where('product_id', $productId)
            ->whereIn('storage_location_id', $locationIds)
            ->sum('quantity');
    }

    // ─────────────────────────────────────────────────────────────────
    //  Form Definition
    // ─────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        $user     = auth()->user();
        $isSeller = $user->hasRole('Seller');

        return $form->schema([

            // ── Order Details ──────────────────────────────────────────────
            Forms\Components\Section::make('Order Details')
                ->schema([
                    Forms\Components\TextInput::make('reference')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->default('ORD-' . strtoupper(substr(uniqid(), -6)))
                        ->maxLength(100),

                    Forms\Components\Select::make('type')
                        ->options([
                            'online' => 'Online Order',
                            'pos'    => 'Point of Sale',
                            'direct' => 'Direct Sale',
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

            // ── Customer & Branch ──────────────────────────────────────────
            Forms\Components\Section::make('Customer & Branch')
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->maxLength(255),

                    // ── Processed By (locked to authenticated user) ──
                    // We display the current user's name as read-only text.
                    // The actual user_id is injected server-side in mutateFormDataBeforeSave / afterCreate.
                    Forms\Components\TextInput::make('_processed_by_display')
                        ->label('Processed By')
                        ->default(fn () => auth()->user()->name)
                        ->disabled()
                        ->dehydrated(false)  // Never submitted to DB.
                        ->helperText('Automatically set to the logged-in user.'),

                    // ── Branch Selection ──
                    // Sellers: disabled (auto-resolved from their assignment).
                    // Managers / SuperAdmin: selectable from authorised branches.
                    Forms\Components\Select::make('branch_id')
                        ->label('Branch')
                        ->options(fn () => static::getAuthorizedBranches()->pluck('name', 'id')->toArray())
                        ->default(function () use ($isSeller, $user) {
                            if ($isSeller) {
                                return $user->branches()->first()?->id;
                            }
                            return null;
                        })
                        ->disabled($isSeller)
                        ->dehydrated(true)   // Always sent to controller.
                        ->required()
                        ->live()             // Triggers product/location refresh.
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            // Reset location and items when branch changes.
                            $set('storage_location_id', null);
                        })
                        ->helperText($isSeller
                            ? 'Automatically set to your assigned branch.'
                            : 'Select the branch you are selling from.'),

                    // ── Storage Location ──
                    Forms\Components\Select::make('storage_location_id')
                        ->label('Fulfillment Location')
                        ->options(fn (Forms\Get $get) => static::getLocationOptionsForBranch($get('branch_id')))
                        ->searchable()
                        ->required()
                        ->live(),
                ])->columns(2),

            // ── Order Items ────────────────────────────────────────────────
            Forms\Components\Section::make('Order Items')
                ->schema([
                    Forms\Components\Repeater::make('lines')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Product')
                                ->options(fn (Forms\Get $get) => static::getProductOptionsForBranch($get('../../branch_id')))
                                ->required()
                                ->live()
                                ->searchable()
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('unit_price', $product->selling_price ?? 0);
                                        $qty = $get('quantity') ?: 1;
                                        $set('total', $qty * ($product->selling_price ?? 0));

                                        // Recalculate order subtotal.
                                        $lines    = $get('../../lines') ?? [];
                                        $subtotal = collect($lines)->reduce(fn ($s, $l) => $s + ((float)($l['quantity'] ?? 1) * (float)($l['unit_price'] ?? 0)), 0);
                                        $set('../../subtotal', $subtotal);
                                        $set('../../total', $subtotal - (float)($get('../../discount') ?? 0) + (float)($get('../../tax') ?? 0));
                                    }
                                }),

                            Forms\Components\TextInput::make('quantity')
                                ->numeric()->step('any')
                                ->required()
                                ->default(1)
                                ->minValue(0.01)
                                ->live(onBlur: true)
                                ->helperText(function (Forms\Get $get) {
                                    $productId = $get('product_id');
                                    $branchId  = $get('../../branch_id');
                                    if (!$productId || !$branchId) {
                                        return 'Select a product and branch first.';
                                    }
                                    $available = static::getAvailableQty((int) $productId, (int) $branchId);
                                    return "📦 Branch stock: {$available} units";
                                })
                                ->rules([
                                    fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $productId = $get('product_id');
                                        $branchId  = $get('../../branch_id');
                                        if (!$productId || !$branchId || !$value) {
                                            return;
                                        }
                                        $available = static::getAvailableQty((int) $productId, (int) $branchId);
                                        if ((float) $value > (float) $available) {
                                            $fail("Only {$available} units available in this branch.");
                                        }
                                    },
                                ])
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('total', $state * ($get('unit_price') ?? 0));
                                    $lines    = $get('../../lines') ?? [];
                                    $subtotal = collect($lines)->reduce(fn ($s, $l) => $s + ((float)($l['quantity'] ?? 1) * (float)($l['unit_price'] ?? 0)), 0);
                                    $set('../../subtotal', $subtotal);
                                    $set('../../total', $subtotal - (float)($get('../../discount') ?? 0) + (float)($get('../../tax') ?? 0));
                                }),

                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()->step('any')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('total', $state * ($get('quantity') ?? 1));
                                    $lines    = $get('../../lines') ?? [];
                                    $subtotal = collect($lines)->reduce(fn ($s, $l) => $s + ((float)($l['quantity'] ?? 1) * (float)($l['unit_price'] ?? 0)), 0);
                                    $set('../../subtotal', $subtotal);
                                    $set('../../total', $subtotal - (float)($get('../../discount') ?? 0) + (float)($get('../../tax') ?? 0));
                                }),

                            Forms\Components\TextInput::make('total')
                                ->numeric()->step('any')
                                ->required()
                                ->readOnly(),
                        ])
                        ->columns(4)
                        ->defaultItems(1)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $lines    = $get('lines') ?? [];
                            $subtotal = collect($lines)->reduce(fn ($s, $l) => $s + ((float)($l['quantity'] ?? 1) * (float)($l['unit_price'] ?? 0)), 0);
                            $set('subtotal', $subtotal);
                            $set('total', $subtotal - (float)($get('discount') ?? 0) + (float)($get('tax') ?? 0));
                        })
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data) {
                            $data['unit_cost'] = Product::find($data['product_id'])?->purchase_cost ?? 0;
                            return $data;
                        }),
                ]),

            // ── Financials ─────────────────────────────────────────────────
            Forms\Components\Section::make('Financials')
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->required()->numeric()->step('any')->prefix('Br ')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('discount')
                        ->required()->numeric()->step('any')->prefix('Br ')->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $set('total', (float)($get('subtotal') ?? 0) - (float)($get('discount') ?? 0) + (float)($get('tax') ?? 0));
                        }),
                    Forms\Components\TextInput::make('tax')
                        ->required()->numeric()->step('any')->prefix('Br ')->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $set('total', (float)($get('subtotal') ?? 0) - (float)($get('discount') ?? 0) + (float)($get('tax') ?? 0));
                        }),
                    Forms\Components\TextInput::make('total')
                        ->required()->numeric()->step('any')->prefix('Br ')->default(0)->readOnly(),
                    Forms\Components\TextInput::make('paid_amount')
                        ->required()->numeric()->step('any')->prefix('Br ')->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $paid  = (float)($get('paid_amount') ?? 0);
                            $total = (float)($get('total') ?? 0);
                            if ($total > 0) {
                                $set('payment_status', $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending'));
                            }
                        }),
                ])->columns(5),

            // ── Payments ───────────────────────────────────────────────────
            Forms\Components\Section::make('Payments')
                ->schema([
                    Forms\Components\Repeater::make('payments')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('amount')
                                ->numeric()->step('any')->required()->prefix('Br ')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                    $paid  = collect($get('../../payments') ?? [])->sum('amount');
                                    $set('../../paid_amount', $paid);
                                    $total = (float)($get('../../total') ?? 0);
                                    if ($total > 0) {
                                        $set('../../payment_status', $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending'));
                                    }
                                }),
                            Forms\Components\Select::make('method')
                                ->options([
                                    'cash'          => 'Cash',
                                    'bank_transfer' => 'Bank Transfer',
                                    'credit_card'   => 'Credit Card',
                                    'online'        => 'Online Payment',
                                ])
                                ->required()->default('cash'),
                            Forms\Components\TextInput::make('reference')->maxLength(100),
                            Forms\Components\DatePicker::make('date')->required()->default(today()),
                        ])
                        ->columns(4)->defaultItems(0)->live(onBlur: true)
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            $paid  = collect($get('payments') ?? [])->sum('amount');
                            $set('paid_amount', $paid);
                            $total = (float)($get('total') ?? 0);
                            if ($total > 0) {
                                $set('payment_status', $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'pending'));
                            }
                        }),
                ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  Table Definition
    // ─────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()->sortable()->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->badge()
                    ->color('info'),
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
                Tables\Columns\TextColumn::make('total')->money('ETB')->sortable(),
                Tables\Columns\IconColumn::make('is_stock_deducted')
                    ->label('Stock Deducted')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
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
                // Branch filter — visible to Manager & Super Admin only.
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => static::getAuthorizedBranches()->pluck('name', 'id')->toArray())
                    ->visible(fn () => !auth()->user()->hasRole('Seller')),
            ])
            ->actions([
                Tables\Actions\Action::make('print_invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Sale $record) => route('invoice.show', $record))
                    ->openUrlInNewTab(),

                // ── Confirm & Deduct Stock ──────────────────────────────────
                Tables\Actions\Action::make('confirm_and_deduct')
                    ->label('Confirm & Deduct Stock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Sale $record) => !$record->is_stock_deducted && $record->status === 'pending')
                    ->action(function (Sale $record) {
                        try {
                            app(SaleService::class)->confirmAndDeductStock($record, auth()->user());
                            Notification::make()
                                ->title('Order Confirmed — Stock Deducted')
                                ->body("Sale {$record->reference} confirmed and branch inventory updated.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Cannot Confirm Sale')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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

    // ─────────────────────────────────────────────────────────────────
    //  Pages
    // ─────────────────────────────────────────────────────────────────

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
