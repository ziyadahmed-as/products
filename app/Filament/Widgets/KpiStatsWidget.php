<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StorageLocation;
use App\Models\ManufacturingOrder;
use App\Models\InventoryBalance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class KpiStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected ?string $heading = 'Business Overview';

    protected function getStats(): array
    {
        $user       = auth()->user();
        $isSeller   = $user->hasRole('Seller');
        $isManager  = $user->hasRole('Manager');
        
        $startDateStr = $this->filters['startDate'] ?? null;
        $endDateStr   = $this->filters['endDate'] ?? null;
        $branchId     = $this->filters['branch_id'] ?? null;

        $startDate = $startDateStr ? Carbon::parse($startDateStr)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $endDateStr ? Carbon::parse($endDateStr)->endOfDay() : Carbon::now()->endOfMonth();
        $today     = Carbon::today();

        // Calculate previous period for trend
        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevStartDate = (clone $startDate)->subDays($diffDays);
        $prevEndDate   = (clone $endDate)->subDays($diffDays);

        $salesQuery = Sale::query();

        // Branch filtering
        if ($branchId) {
            $locationIds = StorageLocation::where('branch_id', $branchId)->pluck('id');
            $salesQuery->whereIn('storage_location_id', $locationIds);
        } else {
            if ($isManager || $isSeller) {
                $branchIds = $user->branches()->pluck('branches.id');
                $locationIds = StorageLocation::whereIn('branch_id', $branchIds)->pluck('id');
                $salesQuery->whereIn('storage_location_id', $locationIds);
            }
        }

        if ($isSeller) {
            $salesQuery->where('user_id', $user->id);
        }

        $activeStatuses = ['confirmed', 'processing', 'shipped', 'completed'];
        $salesThisPeriod = (clone $salesQuery)->whereIn('status', $activeStatuses)->whereBetween('created_at', [$startDate, $endDate])->sum('total');
        $salesPrevPeriod = (clone $salesQuery)->whereIn('status', $activeStatuses)->whereBetween('created_at', [$prevStartDate, $prevEndDate])->sum('total');
        $salesTrend = $salesPrevPeriod > 0
            ? round((($salesThisPeriod - $salesPrevPeriod) / $salesPrevPeriod) * 100, 1)
            : 100;

        $ordersToday     = (clone $salesQuery)->whereDate('created_at', $today)->count();
        $pendingOrders   = (clone $salesQuery)->whereIn('status', ['pending'])->count();
        $completedOrders = (clone $salesQuery)->whereBetween('created_at', [$startDate, $endDate])->whereIn('status', $activeStatuses)->count();

        $stats = [
            Stat::make('Revenue (Selected Period)', 'Br ' . number_format($salesThisPeriod, 2))
                ->description(($salesTrend >= 0 ? '? ' : '? ') . abs($salesTrend) . '% vs previous period')
                ->descriptionIcon($salesTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesTrend >= 0 ? 'success' : 'danger')
                ->chart([max(0, $salesPrevPeriod / 1000), max(0, $salesThisPeriod / 1000)]),

            Stat::make('Orders Today', $ordersToday)
                ->description($pendingOrders . ' pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),
                
            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting processing across all time')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($pendingOrders > 5 ? 'danger' : ($pendingOrders > 0 ? 'warning' : 'success')),
        ];

        // --- Non-Seller KPIs ---
        if (!$isSeller) {
            $invQuery = InventoryBalance::query();
            if ($branchId) {
                $locationIds = StorageLocation::where('branch_id', $branchId)->pluck('id');
                $invQuery->whereIn('storage_location_id', $locationIds);
            } else if ($isManager) {
                $branchIds = $user->branches()->pluck('branches.id');
                $locationIds = StorageLocation::whereIn('branch_id', $branchIds)->pluck('id');
                $invQuery->whereIn('storage_location_id', $locationIds);
            }

            $lowStockCount = (clone $invQuery)->whereHas('product', function ($q) {
                $q->whereColumn('inventory_balances.quantity', '<=', 'products.minimum_stock_level');
            })->count();

            $activeProduction = ManufacturingOrder::whereIn('status', ['planned', 'in_progress'])->count();
            $completedThisPeriod = ManufacturingOrder::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])->count();

            $totalActiveProducts = Product::where('is_active', true)->count();

            $stats[] = Stat::make('Low Stock Alerts', $lowStockCount)
                ->description($lowStockCount > 0 ? 'Products below reorder point' : 'All stock levels healthy')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'danger' : 'success');

            $stats[] = Stat::make('Active Products', $totalActiveProducts)
                ->description('Listed in storefront')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info');

            $stats[] = Stat::make('Production Orders', $activeProduction)
                ->description($completedThisPeriod . ' completed in selected period')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color($activeProduction > 0 ? 'warning' : 'gray');
        }

        return $stats;
    }
}
