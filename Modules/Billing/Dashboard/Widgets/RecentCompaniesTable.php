<?php

namespace Modules\Billing\Dashboard\Widgets;

class RecentCompaniesTable extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.recent_companies';
    }

    public function title(): string
    {
        return 'Recent Companies';
    }

    public function type(): string
    {
        return 'table';
    }

    public function size(): string
    {
        return 'col-md-12 col-xl-6';
    }

    public function permission(): string
    {
        return 'view company';
    }

    public function order(): int
    {
        return 205;
    }

    public function payload(): ?array
    {
        $rows = $this->platform->recentCompanies(5)
            ->map(fn ($company) => [
                optional($company->setting)->name ?? '-',
                optional($company->setting)->email ?? '-',
                $this->statusBadge($company->status),
            ])
            ->values()
            ->all();

        return [
            'columns' => ['Company', 'Email', 'Status'],
            'rows' => $rows,
            'empty' => 'No companies yet.',
            'link' => ['label' => 'View all companies', 'url' => route('companies.index')],
        ];
    }
}
