<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Sale;
use App\Models\Product;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SellerPaymentReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Sales';
    protected static ?string $navigationLabel = 'Seller Reports';
    protected static ?string $title = 'Seller Payment Reports';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.seller-payment-report';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isSeller = $user->hasRole('Seller');

        return $table
            ->query(
                Sale::query()
                    // Sellers only see their own sales
                    ->when($isSeller, fn ($q) => $q->where('user_id', $user->id))
                    ->latest()
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Invoice #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable()
                    ->visible(!$isSeller),
                TextColumn::make('lines')
                    ->label('Products & Qty')
                    ->formatStateUsing(function ($record) {
                        return $record->lines->map(function ($line) {
                            return "{$line->quantity}x " . ($line->product->name ?? 'Unknown');
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
                    ->label('Outstanding Balance')
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
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                \Filament\Tables\Actions\Action::make('print')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Sale $record) => route('invoice.show', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}
