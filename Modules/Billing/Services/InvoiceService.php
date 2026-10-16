<?php

namespace Modules\Billing\Services;

use Modules\Billing\Repositories\InvoiceRepository;

class InvoiceService
{
    protected $invoices;

    public function __construct(InvoiceRepository $invoices)
    {
        $this->invoices = $invoices;
    }

    public function getInvoices()
    {
        return $this->invoices->dataTable($this->invoices->query()->latest('id'));
    }
}
