<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Modules\Payroll\Models\Deduction;

class DeductionRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new Deduction());

        $this->dataTableEditColumns = [
            'status' => function ($deduction) {
                return $this->dt->statusBadge(
                    ucfirst($deduction->status),
                    $deduction->status == 'active' ? 'btn-success' : 'btn-warning'
                );
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($deduction) {
                $btns = [];
                if (auth()->user()->hasPermission('edit deduction')) {
                    $btns[] = $this->dt->editButton(
                        route('deductions.edit', $deduction->uid),
                        'edit deduction'
                    );
                }
                if (auth()->user()->hasPermission('delete deduction')) {
                    $btns[] = $this->dt->deleteButton(
                        $deduction->uid,
                        $deduction->name,
                        'delete deduction'
                    );
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * Whether the company already has a deduction with this name.
     *
     * @param  int    $companyId
     * @param  string $name
     * @param  int|null  $exceptId
     * @return bool
     */
    public function nameExistsForCompany($companyId, $name, $exceptId = null)
    {
        $query = $this->query()
            ->where('company_id', $companyId)
            ->where('name', $name);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    /**
     * Active deductions for a company (select options).
     *
     * @param  int  $companyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeForCompany($companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}