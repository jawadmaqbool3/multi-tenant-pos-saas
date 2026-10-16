<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class AverageSalesChart extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'sales.avg_daily';
    }

    public function title(): string
    {
        return 'Average Daily Sales';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function permission(): string
    {
        return 'dashboard daily average sales';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-6';
    }

    public function order(): int
    {
        return 20;
    }

    protected function payload(): ?array
    {
        return $this->home->averageSalesChart();
    }
}
