<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use App\Models\FilterLog;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    protected ?string $heading = 'Analytics';

    protected ?string $description = 'An overview of some analytics.';
    protected function getStats(): array
    {
        return [
            Stat::make('Flagged Posts', Post::where('status', 'flagged')->count())
                ->description('Total flagged posts')
                ->color('danger')
                ->icon('heroicon-o-flag'),

            Stat::make('Flagged Comments', Comment::where('status', 'flagged')->count())
                ->description('Total flagged comments')
                ->color('warning')
                ->icon('heroicon-o-chat-bubble-bottom-center-text'),

            Stat::make('Filter Logs', FilterLog::count())
                ->description('Total filter logs')
                ->color('info')
                ->icon('heroicon-o-document-text'),
        ];
    }

}
