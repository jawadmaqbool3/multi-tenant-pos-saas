<?php

namespace Modules\Billing\Dashboard\Widgets;

class DueInvoicesTable extends PlatformWidget
{
    public function key(): string
    {
        return 'platform.due_invoices';
    }

    public function title(): string
    {
        return 'Due Invoices';
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
        return 'view billing invoice';
    }

    public function order(): int
    {
        return 213;
    }

    public function payload(): ?array
    {
        $rows = $this->platform->dueInvoices(8)
            ->map(fn ($invoice) => [
                optional(optional($invoice->company)->setting)->name ?? '-',
                ['text' => number_format($invoice->amount_due / 100, 2), 'format' => 'text'],
                ['text' => $invoice->due_date ? date('d M Y', strtotime($invoice->due_date)) : '-', 'format' => 'text'],
                $this->statusBadge($invoice->status),
            ])
            ->values()
            ->all();

        return [
            'columns' => ['Company', 'Amount Due', 'Due Date', 'Status'],
            'rows' => $rows,
            'empty' => 'No invoices due.',
        ];
    }
}
