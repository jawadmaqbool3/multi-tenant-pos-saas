<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Invoice;

class InvoiceRepository extends BaseRepository
{
    protected $dataTableWith = ['company'];
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new Invoice());

        $this->dataTableEditColumns = [
            'company_name' => function ($invoice) {
                return optional($invoice->company)->name;
            },
            'amount' => function ($invoice) {
                $amount = $invoice->amount_paid ?: $invoice->amount_due;
                return number_format($amount / 100, 2) . ' ' . strtoupper($invoice->currency);
            },
            'status' => function ($invoice) {
                return $this->dt->statusBadge($invoice->status, $this->statusColor($invoice->status));
            },
        ];

        $this->dataTableAddColumns = [
            'action' => function ($invoice) {
                $btns = [];
                if ($invoice->hosted_invoice_url) {
                    $btns[] = '<a href="' . $invoice->hosted_invoice_url . '" target="_blank" title="View invoice"'
                        . ' class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
                        . $this->dt->icon('eye') . '</a>';
                }
                if ($invoice->invoice_pdf) {
                    $btns[] = '<a href="' . $invoice->invoice_pdf . '" target="_blank" title="Download PDF"'
                        . ' class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
                        . $this->dt->icon('download') . '</a>';
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * @param  string  $status
     * @return string
     */
    protected function statusColor($status)
    {
        switch ($status) {
            case 'paid':
                return 'btn-success';
            case 'open':
                return 'btn-warning';
            case 'uncollectible':
                return 'btn-danger';
            default:
                return 'btn-secondary';
        }
    }
}
