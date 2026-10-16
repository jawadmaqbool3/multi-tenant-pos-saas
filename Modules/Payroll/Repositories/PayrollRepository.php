<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Modules\Payroll\Models\Payroll;

class PayrollRepository extends BaseRepository
{
    protected $dataTableWith = [];
    protected $dataTableRawColumns = ['action', 'period', 'status'];

    public function __construct()
    {
        parent::__construct(new Payroll());

        $this->dataTableEditColumns = [
            'period' => function ($payroll) {
                return \Illuminate\Support\Carbon::parse($payroll->period)->format('F Y');
            },
            'status' => function ($payroll) {
                $badge = [
                    'draft' => 'btn-secondary',
                    'processed' => 'btn-warning',
                    'paid' => 'btn-success',
                ][$payroll->status] ?? 'btn-secondary';

                return $this->dt->statusBadge(ucwords($payroll->status), $badge);
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($payroll) {
                $btns = [];
                if (auth()->user()->hasPermission('edit payroll')) {
                    $btns[] = $this->dt->editButton(route('payrolls.show', $payroll->uid), 'edit payroll', null, 'Show');
                }
                if ($payroll->status === 'draft' && auth()->user()->hasPermission('delete payroll')) {
                    $btns[] = $this->dt->deleteButton($payroll->uid, \Illuminate\Support\Carbon::parse($payroll->period)->format('F Y'), 'delete payroll');
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}