<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Recent Orders';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isSeller = $user->hasRole('Seller');

        return $table
            ->query(
                Sale::query()
                    ->when($isSeller, fn ($q) => $q->where('user_id', $user->id))
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->placeholder('Guest')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'info', 'pos' => 'success', 'direct' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray', 'confirmed' => 'info', 'processing' => 'warning',
                        'shipped' => 'primary', 'completed' => 'success', 'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success', 'partial' => 'warning', 'pending' => 'gray', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Sale $record) => route('filament.admin.resources.sales.edit', $record))
                    ->icon('heroicon-m-arrow-top-right-on-square'),
            ]);
    }
}
