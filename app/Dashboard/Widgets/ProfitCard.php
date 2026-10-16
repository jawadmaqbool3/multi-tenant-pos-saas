<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\AbstractWidget;
use App\Services\HomeService;

class ProfitCard extends AbstractWidget
{
    protected HomeService $home;

    public function __construct(HomeService $home)
    {
        $this->home = $home;
    }

    public function key(): string
    {
        return 'coa.today_profit';
    }

    public function title(): string
    {
        return 'Today Profit';
    }

    public function type(): string
    {
        return 'chart';
    }

    public function permission(): string
    {
        return 'dashboard profit loss report';
    }

    public function size(): string
    {
        return 'col-md-6 col-xl-6';
    }

    public function order(): int
    {
        return 10;
    }

    protected function payload(): ?array
    {
        return $this->home->profitCard();
    }
}
