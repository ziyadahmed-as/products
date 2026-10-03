<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Monthly Revenue (Last 6 Months)';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $user = auth()->user();
        $isSeller = $user->hasRole('Seller');
        
        $data   = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month  = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[]   = Sale::query()
                ->when($isSeller, fn ($q) => $q->where('user_id', $user->id))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total');
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
