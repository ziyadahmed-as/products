<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use App\Models\StorageLocation;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class OrdersByTypeChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected static ?string $heading = 'Orders by Type';
    protected static ?string $maxHeight = '250px';

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

        $query = Sale::query()->whereBetween('created_at', [$startDate, $endDate]);

        if ($branchId) {
            $locationIds = StorageLocation::where('branch_id', $branchId)->pluck('id');
            $query->whereIn('storage_location_id', $locationIds);
        } else if ($isManager || $isSeller) {
            $branchIds = $user->branches()->pluck('branches.id');
            $locationIds = StorageLocation::whereIn('branch_id', $branchIds)->pluck('id');
            $query->whereIn('storage_location_id', $locationIds);
        }

        if ($isSeller) {
            $query->where('user_id', $user->id);
        }

        $online = (clone $query)->where('type', 'online')->count();
        $pos    = (clone $query)->where('type', 'pos')->count();
        $direct = (clone $query)->where('type', 'direct')->count();

        return [
            'datasets' => [
                [
                    'label'           => 'Orders',
                    'data'            => [$online, $pos, $direct],
                    'backgroundColor' => ['#0ea5e9', '#22c55e', '#eab308'],
                ],
            ],
            'labels' => ['Online', 'POS', 'Direct'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
