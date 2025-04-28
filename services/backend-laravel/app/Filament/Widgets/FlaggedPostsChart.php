<?php

namespace App\Filament\Widgets;

use App\Models\FilterLog;
use App\Models\Post;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
class FlaggedPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Flagged Posts Trend';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $data = Trend::model(FilterLog::class)
            ->between(
                start: now()->startOfMonth(),
                end: now()->endOfMonth()
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Flagged Posts',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#FF5733',
                    'borderColor' => '#C70039',
                    'fill' => false,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M d'))->toArray(),         ];
    }

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
    ];

    protected static ?string $pollingInterval = '5s';
}
