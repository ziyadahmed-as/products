<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\ManufacturingOrder;
use App\Models\InventoryBalance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class KpiStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $heading = 'Business Overview';

    protected function getStats(): array
    {
        $today      = Carbon::today();
        $thisMonth  = Carbon::now()->startOfMonth();
        $lastMonth  = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // ─── Sales KPIs ───
        $salesThisMonth = Sale::where('created_at', '>=', $thisMonth)->sum('total');
        $salesLastMonth = Sale::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->sum('total');
        $salesTrend     = $salesLastMonth > 0
            ? round((($salesThisMonth - $salesLastMonth) / $salesLastMonth) * 100, 1)
            : 100;

        $ordersToday     = Sale::whereDate('created_at', $today)->count();
        $pendingOrders   = Sale::where('status', 'pending')->count();

        // ─── Inventory KPIs ───
        $lowStockCount = InventoryBalance::whereHas('product', function ($q) {
            $q->whereColumn('quantity', '<=', 'minimum_stock_level');
        })->count();

        // ─── Manufacturing KPIs ───
        $activeProduction = ManufacturingOrder::whereIn('status', ['planned', 'in_progress'])->count();
        $completedThisMonth = ManufacturingOrder::where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)->count();

        // ─── Product KPIs ───
        $totalActiveProducts = Product::where('is_active', true)->count();

        return [
            Stat::make('Revenue This Month', '$' . number_format($salesThisMonth, 2))
                ->description(($salesTrend >= 0 ? '▲ ' : '▼ ') . abs($salesTrend) . '% vs last month')
                ->descriptionIcon($salesTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesTrend >= 0 ? 'success' : 'danger')
                ->chart([max(0, $salesLastMonth / 1000), max(0, $salesThisMonth / 1000)]),

            Stat::make('Orders Today', $ordersToday)
                ->description($pendingOrders . ' pending approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('Low Stock Alerts', $lowStockCount)
                ->description($lowStockCount > 0 ? 'Products below reorder point' : 'All stock levels healthy')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Active Products', $totalActiveProducts)
                ->description('Listed in storefront')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Production Orders', $activeProduction)
                ->description($completedThisMonth . ' completed this month')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color($activeProduction > 0 ? 'warning' : 'gray'),

            Stat::make('Pending Orders', $pendingOrders)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($pendingOrders > 5 ? 'danger' : ($pendingOrders > 0 ? 'warning' : 'success')),
        ];
    }
}
