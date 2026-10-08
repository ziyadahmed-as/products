<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Sale;
use App\Models\StorageLocation;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SellerPaymentReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Sales Reports';
    protected static ?string $title           = 'Sales & Payment Reports';
    protected static ?int    $navigationSort  = 5;

    protected static string $view = 'filament.pages.seller-payment-report';

    public function table(Table $table): Table
    {
        $user      = auth()->user();
        $isSeller  = $user->hasRole('Seller');
        $isManager = $user->hasRole('Manager');

        return $table
            ->query(
                Sale::query()
                    ->with(['user', 'branch', 'lines.product'])
                    // Seller: only their own sales from their branch.
                    ->when($isSeller, fn ($q) => $q
                        ->where('user_id', $user->id)
                        ->whereIn('branch_id', $user->authorizedBranchIds())
                    )
                    // Manager: sales from their authorised branches only.
                    ->when($isManager && !$isSeller, fn ($q) => $q
                        ->whereIn('branch_id', $user->authorizedBranchIds())
                    )
                    // Super Admin: no restriction.
                    ->latest()
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('user.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable()
                    ->visible(!$isSeller),

                // Products & Quantities column (uses sale_line.quantity, not sale.total).
                TextColumn::make('lines')
                    ->label('Products & Qty')
                    ->formatStateUsing(function ($record) {
                        return $record->lines->map(function ($line) {
                            $productName = $line->product?->name ?? 'Unknown';
                            $qty         = number_format($line->quantity, 2);
                            $unitPrice   = 'Br ' . number_format($line->unit_price, 2);
                            $lineTotal   = 'Br ' . number_format($line->total, 2);
                            return "{$qty}× {$productName} @ {$unitPrice} = {$lineTotal}";
                        })->implode('<br>');
                    })
                    ->html(),

                TextColumn::make('total')
                    ->label('Invoice Total')
                    ->money('ETB')
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Amount Paid')
                    ->money('ETB')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Outstanding')
                    ->state(fn (Sale $record) => $record->total - $record->paid_amount)
                    ->money('ETB')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'     => 'success',
                        'partial'  => 'warning',
                        'pending'  => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'confirmed' => 'info',
                        'pending'   => 'gray',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('is_stock_deducted')
                    ->label('Stock Deducted')
                    ->formatStateUsing(fn ($state) => $state ? '✅ Yes' : '❌ No')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(fn () => \App\Models\Branch::whereIn(
                        'id',
                        $user->authorizedBranchIds()
                    )->pluck('name', 'id')->toArray())
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->where('branch_id', $data['value'])
                        : $query
                    )
                    ->visible(!$isSeller),

                \Filament\Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid'     => 'Paid',
                        'partial'  => 'Partial',
                        'pending'  => 'Pending',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('print')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Sale $record) => route('invoice.show', $record))
                    ->openUrlInNewTab(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
