<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\StorageLocation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RecentOrdersWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected static ?string $heading = 'Orders in Selected Period';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isSeller = $user->hasRole('Seller');
        $isManager = $user->hasRole('Manager');
        
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;
        $branchId  = $this->filters['branch_id'] ?? null;

        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        return $table
            ->query(
                Sale::query()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->when($isSeller, fn ($q) => $q->where('user_id', $user->id))
                    ->when($branchId, function ($q) use ($branchId) {
                        $locationIds = StorageLocation::where('branch_id', $branchId)->pluck('id');
                        return $q->whereIn('storage_location_id', $locationIds);
                    })
                    ->when(!$branchId && ($isManager || $isSeller), function ($q) use ($user) {
                        $branchIds = $user->branches()->pluck('branches.id');
                        $locationIds = StorageLocation::whereIn('branch_id', $branchIds)->pluck('id');
                        return $q->whereIn('storage_location_id', $locationIds);
                    })
                    ->latest()
                    ->limit(10)
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
                    ->money('ETB')
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
