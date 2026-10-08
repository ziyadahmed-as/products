<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\StorageLocation;
use App\Services\SaleService;
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

    protected static ?string $navigationIcon  = 'heroicon-o-bolt';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Quick Sell';
    protected static ?string $title           = 'Quick Sell — Point of Sale';
    protected static ?int    $navigationSort  = 0;
    protected static string  $view            = 'filament.pages.quick-sell';

    /** Visible to Sellers, Managers, and Super Admins who can sell. */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole('Seller') || $user->hasRole('Manager') || $user->hasRole('Super Admin');
    }

    public ?array $data = [];

    /** Pre-selected product from product list "Sell" button. */
    #[Url]
    public ?int $productId = null;

    // ─────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────

    private function currentUser(): \App\Models\User
    {
        return auth()->user();
    }

    /**
     * For a Seller: their single assigned branch (null if unassigned).
     * For Manager/SuperAdmin: null — they choose via the form.
     */
    private function autoResolvedBranch(): ?Branch
    {
        $user = $this->currentUser();
        if ($user->hasRole('Seller')) {
            return $user->branches()->where('is_active', true)->first();
        }
        return null;
    }

    /**
     * Branch options available for the current user in the form.
     */
    private function branchOptions(): array
    {
        $user = $this->currentUser();
        if ($user->hasRole('Super Admin')) {
            return Branch::where('is_active', true)->pluck('name', 'id')->toArray();
        }
        return $user->branches()->where('is_active', true)->pluck('name', 'id')->toArray();
    }

    /**
     * Product options scoped to the given branch_id.
     */
    private function productOptionsForBranch(?int $branchId): array
    {
        if (!$branchId) {
            return [];
        }

        return Product::whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->whereIn('type', ['resale_product', 'manufactured_product'])
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->name . ' — Br ' . number_format($p->selling_price, 2)])
            ->toArray();
    }

    /**
     * Available quantity of a product in the given branch.
     */
    private function availableQty(int $productId, int $branchId): float
    {
        $locationIds = StorageLocation::where('branch_id', $branchId)
            ->where('is_active', true)
            ->pluck('id');

        return (float) InventoryBalance::where('product_id', $productId)
            ->whereIn('storage_location_id', $locationIds)
            ->sum('quantity');
    }

    // ─────────────────────────────────────────────────────────────────
    //  Mount
    // ─────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user        = $this->currentUser();
        $autoBranch  = $this->autoResolvedBranch();
        $branchId    = $autoBranch?->id;

        $defaultLines = [];

        // Pre-fill from URL param if the product belongs to this branch.
        if ($this->productId && $branchId) {
            $allowed = $this->productOptionsForBranch($branchId);
            if (array_key_exists($this->productId, $allowed)) {
                $p = Product::find($this->productId);
                $defaultLines[] = [
                    'product_id' => $p->id,
                    'unit_price' => $p->selling_price ?? 0,
                    'quantity'   => 1,
                    'total'      => $p->selling_price ?? 0,
                ];
            }
        }

        if (empty($defaultLines)) {
            $defaultLines[] = [
                'product_id' => null,
                'unit_price' => 0,
                'quantity'   => 1,
                'total'      => 0,
            ];
        }

        $this->form->fill([
            'branch_id'      => $branchId,
            'customer_name'  => '',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'notes'          => '',
            'lines'          => $defaultLines,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  Form Schema
    // ─────────────────────────────────────────────────────────────────

    public function form(Form $form): Form
    {
        $user       = $this->currentUser();
        $isSeller   = $user->hasRole('Seller');
        $autoBranch = $this->autoResolvedBranch();

        return $form
            ->schema([

                // ── Branch Selection ────────────────────────────────────────
                Forms\Components\Section::make('🏢 Branch')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->label('Selling From Branch')
                            ->options($this->branchOptions())
                            ->default($autoBranch?->id)
                            ->disabled($isSeller)
                            ->dehydrated(true)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('lines', [[
                                'product_id' => null,
                                'unit_price' => 0,
                                'quantity'   => 1,
                                'total'      => 0,
                            ]]))
                            ->helperText($isSeller
                                ? "Branch automatically set to: {$autoBranch?->name}"
                                : 'Select the branch you are selling from.'
                            ),

                        Forms\Components\Placeholder::make('_operator_info')
                            ->label('Operator')
                            ->content(fn () => $user->name . ' (' . implode(', ', $user->getRoleNames()->toArray()) . ')')
                            ->helperText('Automatically set from your login session.'),
                    ])->columns(2)->collapsible(),

                // ── Customer Information ────────────────────────────────────
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
                    ])->columns(3)->collapsible(),

                // ── Order Items ─────────────────────────────────────────────
                Forms\Components\Section::make('🛒 Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('lines')
                            ->label(false)
                            ->schema([
                                // Product dropdown — scoped to selected branch.
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn (Forms\Get $get) => $this->productOptionsForBranch($get('../../branch_id')))
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (!$state) return;
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('unit_price', $product->selling_price ?? 0);
                                            $qty = (float)($get('quantity') ?: 1);
                                            $set('total', $qty * ($product->selling_price ?? 0));
                                        }
                                    })
                                    ->columnSpan(4),

                                // Unit Price
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Price')
                                    ->prefix('Br ')
                                    ->numeric()->step('any')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) =>
                                        $set('total', (float)($state ?? 0) * (float)($get('quantity') ?? 1))
                                    )
                                    ->columnSpan(2),

                                // Quantity — with live branch-stock display and validation.
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()->step('any')
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $set('total', (float)($state ?? 1) * (float)($get('unit_price') ?? 0));
                                    })
                                    ->helperText(function (Forms\Get $get) {
                                        $productId = $get('product_id');
                                        $branchId  = $get('../../branch_id');
                                        if (!$productId || !$branchId) {
                                            return 'Select a product and branch first.';
                                        }
                                        $available = $this->availableQty((int)$productId, (int)$branchId);
                                        $color     = $available <= 0 ? '🔴' : ($available < 10 ? '🟡' : '🟢');
                                        return "{$color} Branch stock: " . number_format($available, 2) . ' units';
                                    })
                                    ->rules([
                                        fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $productId = $get('product_id');
                                            $branchId  = $get('../../branch_id');
                                            if (!$productId || !$branchId || !$value) return;

                                            $available = $this->availableQty((int)$productId, (int)$branchId);
                                            if ((float)$value > (float)$available) {
                                                $productName = Product::find($productId)?->name ?? 'this product';
                                                $fail("Only {$available} units of \"{$productName}\" available in this branch.");
                                            }
                                        },
                                    ])
                                    ->columnSpan(2),

                                // Line Total (read-only)
                                Forms\Components\TextInput::make('total')
                                    ->label('Line Total')
                                    ->prefix('Br ')
                                    ->numeric()->step('any')
                                    ->readOnly()
                                    ->columnSpan(2),
                            ])
                            ->columns(10)
                            ->defaultItems(1)
                            ->addActionLabel('+ Add Product')
                            ->reorderable(false),
                    ]),

                // ── Payment ─────────────────────────────────────────────────
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

    // ─────────────────────────────────────────────────────────────────
    //  Actions
    // ─────────────────────────────────────────────────────────────────

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('✅ Confirm Sale & Deduct Stock')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->size('lg')
                ->submit('submit'),
            Action::make('reset')
                ->label('🔄 Clear Form')
                ->color('gray')
                ->action(fn () => $this->mount()),
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    //  Submit — Atomic Sale + Stock Deduction
    // ─────────────────────────────────────────────────────────────────

    public function submit(): void
    {
        $data    = $this->form->getState();
        $user    = $this->currentUser();
        $service = app(SaleService::class);

        // ── 1. Backend branch authorization ─────────────────────────────
        try {
            $branch = $service->resolveAuthorizedBranch($user, $data['branch_id'] ?? null);
        } catch (\RuntimeException $e) {
            Notification::make()->title('Branch Error')->body($e->getMessage())->danger()->send();
            return;
        }

        // ── 2. Resolve a storage location inside the branch ─────────────
        try {
            $location = $service->resolveStorageLocation($branch);
        } catch (\RuntimeException $e) {
            Notification::make()->title('Location Error')->body($e->getMessage())->danger()->send();
            return;
        }

        $lines = $data['lines'] ?? [];

        // ── 3. Pre-flight stock validation (outside transaction for UX speed) ──
        foreach ($lines as $line) {
            $productId = $line['product_id'] ?? null;
            $qty       = (float)($line['quantity'] ?? 1);
            if (!$productId) continue;

            $available = $this->availableQty((int)$productId, $branch->id);
            if ($qty > $available) {
                $name = Product::find($productId)?->name ?? "Product #{$productId}";
                Notification::make()
                    ->title('Insufficient Stock')
                    ->body("Only {$available} units of \"{$name}\" available in branch \"{$branch->name}\".")
                    ->danger()
                    ->send();
                return;
            }
        }

        // ── 4. Create sale + lines + payment + deduct stock (one transaction) ──
        try {
            $sale = DB::transaction(function () use ($data, $user, $branch, $location, $lines, $service) {
                $subtotal = collect($lines)->sum(fn ($l) => (float)($l['total'] ?? 0));

                // Create the Sale header — user_id always from authenticated session.
                $sale = Sale::create([
                    'reference'           => 'QS-' . strtoupper(substr(uniqid(), -8)),
                    'type'                => 'pos',
                    'user_id'             => $user->id,          // ← Always from session
                    'branch_id'           => $branch->id,        // ← Validated branch
                    'storage_location_id' => $location->id,
                    'customer_name'       => $data['customer_name'] ?: null,
                    'subtotal'            => $subtotal,
                    'discount'            => 0,
                    'tax'                 => 0,
                    'total'               => $subtotal,
                    'paid_amount'         => $data['payment_status'] === 'paid' ? $subtotal : 0,
                    'payment_status'      => $data['payment_status'],
                    'status'              => 'confirmed',
                    'is_stock_deducted'   => false,
                ]);

                // Create SaleLines — quantity from form, NOT from financial total.
                foreach ($lines as $line) {
                    $productId = $line['product_id'];
                    $qty       = (float)($line['quantity'] ?? 1);
                    $price     = (float)($line['unit_price'] ?? 0);
                    $product   = Product::find($productId);

                    SaleLine::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $productId,
                        'quantity'   => $qty,         // ← The actual quantity sold
                        'unit_price' => $price,
                        'unit_cost'  => $product?->purchase_cost ?? 0,
                        'discount'   => 0,
                        'total'      => (float)($line['total'] ?? $qty * $price),
                    ]);
                }

                // Create Payment record if paid.
                if (($data['payment_status'] ?? 'pending') !== 'pending') {
                    Payment::create([
                        'sale_id'   => $sale->id,
                        'amount'    => $subtotal,
                        'method'    => $data['payment_method'] ?? 'cash',
                        'reference' => $sale->reference,
                        'date'      => today(),
                    ]);
                }

                // Reload lines relationship for stock deduction.
                $sale->load('lines');

                // Deduct branch stock atomically using the sale_line.quantity values.
                $service->deductBranchStock($branch, $sale, $user);

                // Mark stock as deducted.
                $sale->updateQuietly(['is_stock_deducted' => true]);

                return $sale;
            });

            Notification::make()
                ->title('✅ Sale Completed!')
                ->body("Order {$sale->reference} recorded. Branch \"{$branch->name}\" inventory updated.")
                ->success()
                ->send();

        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('Sale Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return;
        }

        // Reset form for next sale.
        $this->mount();
    }
}
