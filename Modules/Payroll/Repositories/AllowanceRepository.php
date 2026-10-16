<?php

namespace Modules\Payroll\Repositories;

use App\Repositories\BaseRepository;
use Modules\Payroll\Models\Allowance;

class AllowanceRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'status'];

    public function __construct()
    {
        parent::__construct(new Allowance());

        $this->dataTableEditColumns = [
            'status' => function ($allowance) {
                return $this->dt->statusBadge(
                    ucfirst($allowance->status),
                    $allowance->status == 'active' ? 'btn-success' : 'btn-warning'
                );
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($allowance) {
                $btns = [];
                if (auth()->user()->hasPermission('edit allowance')) {
                    $btns[] = $this->dt->editButton(
                        route('allowances.edit', $allowance->uid),
                        'edit allowance'
                    );
                }
                if (auth()->user()->hasPermission('delete allowance')) {
                    $btns[] = $this->dt->deleteButton(
                        $allowance->uid,
                        $allowance->name,
                        'delete allowance'
                    );
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    /**
     * Whether the company already has an allowance with this name.
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
     * Active allowances for a company (select options).
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