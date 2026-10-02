<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersByTypeChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Orders by Type (This Month)';
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $start = Carbon::now()->startOfMonth();

        $online  = Sale::where('type', 'online')->where('created_at', '>=', $start)->count();
        $pos     = Sale::where('type', 'pos')->where('created_at', '>=', $start)->count();
        $direct  = Sale::where('type', 'direct')->where('created_at', '>=', $start)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data'  => [$online, $pos, $direct],
                    'backgroundColor' => ['#06b6d4', '#6366f1', '#f59e0b'],
                ],
            ],
            'labels' => ['Online', 'Point of Sale', 'Direct'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
