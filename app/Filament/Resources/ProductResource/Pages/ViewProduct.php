<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\InventoryBalance;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ── KPI / Stock Overview ───────────────────────────────────
                Section::make('📦 Stock Overview by Branch & Location')
                    ->description('Real-time inventory levels tracked per branch and storage location.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema(function () {
                        /** @var \App\Models\Product $product */
                        $product = $this->record;
                        $unit    = optional($product->unit)->abbreviation ?? optional($product->unit)->name ?? '';

                        $balances = InventoryBalance::with(['storageLocation.branch'])
                            ->where('product_id', $product->id)
                            ->get();

                        if ($balances->isEmpty()) {
                            return [
                                TextEntry::make('no_stock')
                                    ->label('')
                                    ->default('No inventory records found. Assign branches and add initial quantity.')
                                    ->columnSpanFull(),
                            ];
                        }

                        $byBranch = $balances->groupBy(
                            fn ($b) => optional(optional($b->storageLocation)->branch)->name ?? 'Unassigned'
                        );

                        $entries = [];

                        foreach ($byBranch as $branchName => $branchBalances) {
                            $locationEntries = collect($branchBalances)->map(function ($balance) use ($unit) {
                                $locationName = optional($balance->storageLocation)->name ?? 'Unknown Location';
                                $qty          = number_format((float) $balance->quantity, 2);

                                return TextEntry::make("balance_{$balance->id}")
                                    ->label($locationName)
                                    ->default("{$qty} {$unit}")
                                    ->badge()
                                    ->color((float) $balance->quantity > 0 ? 'success' : 'danger')
                                    ->icon((float) $balance->quantity > 0 ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle');
                            })->all();

                            $branchTotal     = $branchBalances->sum('quantity');
                            $locationEntries[] = TextEntry::make("branch_total_{$branchName}")
                                ->label("🏢 {$branchName} — Total")
                                ->default(number_format((float) $branchTotal, 2) . ' ' . $unit)
                                ->weight(FontWeight::Bold)
                                ->badge()
                                ->color((float) $branchTotal > 0 ? 'primary' : 'danger')
                                ->columnSpanFull();

                            $entries[] = Section::make("Branch: {$branchName}")
                                ->icon('heroicon-o-map-pin')
                                ->schema($locationEntries)
                                ->columns(3);
                        }

                        $grandTotal = $balances->sum('quantity');
                        $entries[]  = TextEntry::make('grand_total_qty')
                            ->label('Total Stock — All Branches')
                            ->default(number_format((float) $grandTotal, 2) . ' ' . $unit)
                            ->weight(FontWeight::Bold)
                            ->badge()
                            ->color((float) $grandTotal > 0 ? 'success' : 'danger')
                            ->columnSpanFull();

                        return $entries;
                    })
                    ->collapsible()
                    ->columns(1),

                // ── General Info ───────────────────────────────────────────
                Section::make('General Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextEntry::make('name')
                            ->weight(FontWeight::Bold)
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('sku')
                            ->label('SKU / Item Code')
                            ->copyable(),
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'manufactured_product' => 'Manufactured Product',
                                'resale_product'       => 'Resale Product',
                                'raw_material'         => 'Raw Material',
                                'consumable'           => 'Consumable',
                                default                => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'manufactured_product' => 'success',
                                'resale_product'       => 'info',
                                'raw_material'         => 'gray',
                                'consumable'           => 'warning',
                                default                => 'gray',
                            }),
                        TextEntry::make('category.name')
                            ->label('Category')
                            ->placeholder('—'),
                        TextEntry::make('unit.name')
                            ->label('Unit of Measure')
                            ->formatStateUsing(function ($state, $record) {
                                $abbr = optional($record->unit)->abbreviation;
                                return $abbr ? "{$state} ({$abbr})" : ($state ?? '—');
                            })
                            ->placeholder('—'),
                        TextEntry::make('package_size')
                            ->placeholder('—'),
                    ])
                    ->columns(3),

                // ── Pricing ────────────────────────────────────────────────
                Section::make('Pricing & Stock Value')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        TextEntry::make('purchase_cost')
                            ->label('Purchase / Production Cost')
                            ->money('ETB'),
                        TextEntry::make('selling_price')
                            ->label('Selling Price')
                            ->money('ETB')
                            ->placeholder('—'),
                        TextEntry::make('quantity')
                            ->label('Total Quantity')
                            ->formatStateUsing(function ($state, $record) {
                                $unit = optional($record->unit)->abbreviation ?? optional($record->unit)->name ?? '';
                                return number_format((float) $state, 2) . ($unit ? " {$unit}" : '');
                            })
                            ->badge()
                            ->color(fn ($state): string => (float) $state > 0 ? 'success' : 'danger'),
                        TextEntry::make('minimum_stock_level')
                            ->label('Reorder Point')
                            ->formatStateUsing(function ($state, $record) {
                                $unit = optional($record->unit)->abbreviation ?? optional($record->unit)->name ?? '';
                                return number_format((float) $state, 2) . ($unit ? " {$unit}" : '');
                            }),
                    ])
                    ->columns(2),

                // ── Branches ───────────────────────────────────────────────
                Section::make('Assigned Branches')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextEntry::make('branches.name')
                            ->label('Branches')
                            ->badge()
                            ->separator(',')
                            ->placeholder('No branches assigned'),
                    ])
                    ->columns(1),

                // ── Image / Specification ──────────────────────────────────
                Section::make('Description & Image')
                    ->icon('heroicon-o-photo')
                    ->visible(fn ($record) => $record->type !== 'raw_material')
                    ->schema([
                        ImageEntry::make('image')
                            ->circular()
                            ->size(120)
                            ->visible(fn ($record) => in_array($record->type, ['resale_product', 'manufactured_product']))
                            ->placeholder('No image uploaded'),
                        TextEntry::make('specification')
                            ->label('Specification / Description')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
