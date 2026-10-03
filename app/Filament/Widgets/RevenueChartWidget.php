<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\StorageLocation;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected static ?string $heading = 'Revenue Trend';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $user = auth()->user();
        $isSeller = $user->hasRole('Seller');
        $isManager = $user->hasRole('Manager');
        
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;
        $branchId  = $this->filters['branch_id'] ?? null;

        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        $data   = [];
        $labels = [];

        // Determine step size (days if within 1 month, months otherwise)
        $diffDays = $startDate->diffInDays($endDate);
        
        if ($diffDays <= 31) {
            $period = \Carbon\CarbonPeriod::create($startDate, '1 day', $endDate);
            foreach ($period as $date) {
                $labels[] = $date->format('M d');
                
                $salesQuery = Sale::query()
                    ->whereDate('created_at', $date);

                if ($branchId) {
                    $locationIds = StorageLocation::where('branch_id', $branchId)->pluck('id');
                    $salesQuery->whereIn('storage_location_id', $locationIds);
                } else if ($isManager || $isSeller) {
                    $branchIds = $user->branches()->pluck('branches.id');
                    $locationIds = StorageLocation::whereIn('branch_id', $branchIds)->pluck('id');
                    $salesQuery->whereIn('storage_location_id', $locationIds);
                }

                if ($isSeller) {
                    $salesQuery->where('user_id', $user->id);
                }

                $data[] = $salesQuery->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])->sum('total');
            }
        } else {
            $period = \Carbon\CarbonPeriod::create($startDate, '1 month', $endDate);
            foreach ($period as $date) {
                $labels[] = $date->format('M Y');
                
                $salesQuery = Sale::query()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);

                if ($branchId) {
                    $locationIds = StorageLocation::where('branch_id', $branchId)->pluck('id');
                    $salesQuery->whereIn('storage_location_id', $locationIds);
                } else if ($isManager || $isSeller) {
                    $branchIds = $user->branches()->pluck('branches.id');
                    $locationIds = StorageLocation::whereIn('branch_id', $branchIds)->pluck('id');
                    $salesQuery->whereIn('storage_location_id', $locationIds);
                }

                if ($isSeller) {
                    $salesQuery->where('user_id', $user->id);
                }

                $data[] = $salesQuery->whereIn('status', ['confirmed', 'processing', 'shipped', 'completed'])->sum('total');
            }
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (Br)',
                    'data'            => $data,
                    'borderColor'     => '#06b6d4',
                    'backgroundColor' => 'rgba(6,182,212,0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
