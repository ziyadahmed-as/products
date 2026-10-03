<?php

namespace App\Filament\Pages;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StockMovement;
use App\Models\StorageLocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

class QuickSell extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon    = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup   = 'Sales';
    protected static ?string $navigationLabel   = 'Quick Sell';
    protected static ?string $title             = 'Quick Sell';
    protected static ?int    $navigationSort    = 0;
    protected static string  $view              = 'filament.pages.quick-sell';

    /** Only visible to Sellers */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Seller');
    }

    public ?array $data = [];

    // Pre-selected product from Products list "Sell" button
    #[Url]
    public ?int $productId = null;

    public function mount(): void
    {
        $user      = auth()->user();
        $branchIds = $user->branches()->pluck('branches.id');

        $productIds = Product::whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds))
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->pluck('id')
            ->toArray();

        $defaultLines = [];

        // If arriving from Products list with a pre-selected product
        if ($this->productId && in_array($this->productId, $productIds)) {
            $p = Product::find($this->productId);
            $defaultLines[] = [
                'product_id' => $p->id,
                'unit_price' => $p->selling_price ?? 0,
                'quantity'   => 1,
                'total'      => $p->selling_price ?? 0,
            ];
        } else {
            $defaultLines[] = [
                'product_id' => null,
                'unit_price' => 0,
                'quantity'   => 1,
                'total'      => 0,
            ];
        }

        $this->form->fill([
            'customer_name'    => '',
            'customer_phone'   => '',
            'customer_email'   => '',
            'payment_method'   => 'cash',
            'payment_status'   => 'paid',
            'notes'            => '',
            'lines'            => $defaultLines,
        ]);
    }

    public function form(Form $form): Form
    {
        $user      = auth()->user();
        $branchIds = $user->branches()->pluck('branches.id');

        $productOptions = Product::whereHas('branches', fn ($q) => $q->whereIn('branches.id', $branchIds))
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->name . ' — $' . number_format($p->selling_price, 2)])
            ->toArray();

        $productPrices = Product::whereIn('id', array_keys($productOptions))
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->selling_price ?? 0])
            ->toArray();

        return $form
            ->schema([
                Forms\Components\Section::make('👤 Customer Information')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Full Name')
                            ->placeholder('Walk-in customer')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_phone')
                            ->label('Phone Number')
                            ->tel()
                            ->placeholder('+1 234 567 8900')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email Address')
                            ->email()
                            ->placeholder('customer@email.com')
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('🛒 Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('lines')
                            ->label(false)
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options($productOptions)
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) use ($productPrices) {
                                        $price = $productPrices[$state] ?? 0;
                                        $set('unit_price', $price);
                                        $set('total', $price * 1);
                                    })
                                    ->columnSpan(4),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Price')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) =>
                                        $set('total', (float)($state ?? 0) * (float)($get('quantity') ?? 1))
                                    )
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $set('total', (float)($state ?? 1) * (float)($get('unit_price') ?? 0));
                                    })
                                    ->rules([
                                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $productId = $get('product_id');
                                            if (!$productId || !$value) return;

                                            $user      = auth()->user();
                                            $branchIds = $user->branches()->pluck('branches.id');
                                            $locationIds = StorageLocation::whereIn('branch_id', $branchIds)
                                                ->where('is_active', true)->pluck('id');

                                            // Sum total available across ALL branch locations
                                            $available = InventoryBalance::where('product_id', $productId)
                                                ->whereIn('storage_location_id', $locationIds)
                                                ->sum('quantity');

                                            if ((float)$value > (float)$available) {
                                                $fail("Only {$available} units available in your branch stock.");
                                            }
                                        },
                                    ])
                                    ->helperText(function (Forms\Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) return 'Select a product first';

                                        $user        = auth()->user();
                                        $branchIds   = $user->branches()->pluck('branches.id');
                                        $locationIds = StorageLocation::whereIn('branch_id', $branchIds)
                                            ->where('is_active', true)->pluck('id');

                                        // Sum total across all branch locations
                                        $available = InventoryBalance::where('product_id', $productId)
                                            ->whereIn('storage_location_id', $locationIds)
                                            ->sum('quantity');

                                        return '📦 Available in branch: ' . number_format((float)$available, 2) . ' units';
                                    })
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('total')
                                    ->label('Line Total')
                                    ->prefix('$')
                                    ->numeric()
                                    ->readOnly()
                                    ->columnSpan(2),
                            ])
                            ->columns(10)
                            ->defaultItems(1)
                            ->addActionLabel('+ Add Another Product')
                            ->reorderable(false),
                    ]),

                Forms\Components\Section::make('💳 Payment')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash'   => '💵 Cash',
                                'card'   => '💳 Card',
                                'bank'   => '🏦 Bank Transfer',
                                'mobile' => '📱 Mobile Payment',
                            ])
                            ->required()
                            ->default('cash'),
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'paid'    => '✅ Paid in Full',
                                'partial' => '⏳ Partial Payment',
                                'pending' => '🕐 Pending',
                            ])
                            ->required()
                            ->default('paid'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes / Remarks')
                            ->placeholder('Optional notes about this sale...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Confirm Sale & Deduct Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->size('lg')
                ->submit('submit'),
            Action::make('reset')
                ->label('Clear Form')
                ->color('gray')
                ->action(fn () => $this->mount()),
        ];
    }

    public function submit(): void
    {
        $data      = $this->form->getState();
        $user      = auth()->user();
        $branchIds = $user->branches()->pluck('branches.id');

        $location = StorageLocation::whereIn('branch_id', $branchIds)
            ->where('is_active', true)
            ->first();

        if (!$location) {
            Notification::make()
                ->title('No Storage Location')
                ->body('Your branch has no active storage location. Contact your administrator.')
                ->danger()->send();
            return;
        }

        // Validate stock at branch level (sum across all locations)
        foreach ($data['lines'] as $line) {
            $productId   = $line['product_id'];
            $qty         = (float)($line['quantity'] ?? 1);
            $locationIds = StorageLocation::whereIn('branch_id', $branchIds)
                ->where('is_active', true)->pluck('id');
            $available   = InventoryBalance::where('product_id', $productId)
                ->whereIn('storage_location_id', $locationIds)
                ->sum('quantity');
            $product     = Product::find($productId);

            if ((float)$available < $qty) {
                Notification::make()
                    ->title('Insufficient Branch Stock: ' . ($product->name ?? 'Unknown'))
                    ->body("Branch total available: {$available} units. You tried to sell: {$qty} units.")
                    ->danger()->send();
                return;
            }
        }

        DB::transaction(function () use ($data, $user, $branchIds) {
            $subtotal    = collect($data['lines'])->sum(fn ($l) => (float)($l['total'] ?? 0));
            $locationIds = StorageLocation::whereIn('branch_id', $branchIds)
                ->where('is_active', true)->pluck('id');
            // Use first location as the sale's primary location
            $primaryLocation = StorageLocation::whereIn('branch_id', $branchIds)
                ->where('is_active', true)->first();

            $sale = Sale::create([
                'reference'           => 'ORD-' . strtoupper(substr(uniqid(), -6)),
                'type'                => 'pos',
                'user_id'             => $user->id,
                'storage_location_id' => $primaryLocation->id,
                'customer_name'       => $data['customer_name'] ?: null,
                'subtotal'            => $subtotal,
                'discount'            => 0,
                'tax'                 => 0,
                'total'               => $subtotal,
                'paid_amount'         => $data['payment_status'] === 'paid' ? $subtotal : 0,
                'payment_status'      => $data['payment_status'],
                'status'              => 'confirmed',
            ]);

            foreach ($data['lines'] as $line) {
                $productId = $line['product_id'];
                $qtyToDeduct = (float)($line['quantity'] ?? 1);
                $originalQty = $qtyToDeduct;
                $unitPrice = (float)($line['unit_price'] ?? 0);
                $lineTotal = (float)($line['total'] ?? 0);
                $product   = Product::find($productId);

                SaleLine::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $productId,
                    'quantity'   => $originalQty,
                    'unit_price' => $unitPrice,
                    'unit_cost'  => $product->purchase_cost ?? 0,
                    'discount'   => 0,
                    'total'      => $lineTotal,
                ]);

                // Deduct inventory across branch locations sequentially
                $balances = InventoryBalance::where('product_id', $productId)
                    ->whereIn('storage_location_id', $locationIds)
                    ->where('quantity', '>', 0)
                    ->orderBy('quantity', 'desc')
                    ->get();
                
                foreach ($balances as $balance) {
                    if ($qtyToDeduct <= 0) break;
                    
                    $deduct = min($balance->quantity, $qtyToDeduct);
                    $balance->decrement('quantity', $deduct);
                    $qtyToDeduct -= $deduct;
                    
                    // Log stock movement for this specific location
                    StockMovement::create([
                        'product_id'          => $productId,
                        'storage_location_id' => $balance->storage_location_id,
                        'type'                => 'out',
                        'quantity'            => $deduct,
                        'unit_cost'           => $product->purchase_cost ?? 0,
                        'user_id'             => $user->id,
                        'reference_type'      => 'sale',
                        'reference_id'        => $sale->id,
                    ]);
                }
            }
        });

        // Sync quantity column on each sold product
        foreach ($data['lines'] as $line) {
            $p = Product::find($line['product_id']);
            if ($p) $p->syncQuantity();
        }

        Notification::make()
            ->title('✅ Sale Completed!')
            ->body('Order recorded and stock updated successfully.')
            ->success()
            ->send();

        // Reset form
        $this->mount();
    }
}
