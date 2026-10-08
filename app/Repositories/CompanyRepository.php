<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository extends BaseRepository
{
    protected $dataTableWith = ['setting'];
    protected $dataTableRawColumns = ['action', 'setting.logo', 'status'];

    public function __construct()
    {
        parent::__construct(new Company());

        $this->dataTableEditColumns = [
            'status' => function ($company) {
                return $this->statusButton($company->status, $company->status == 'active' ? 'btn-success' : 'btn-danger');
            },
            'setting.logo' => function ($company) {
                return $this->thumbnail('assets/media/small/logo', @$company->setting->logo);
            },
            'setting.name' => function ($company) {
                return $company->setting->name ?? null;
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($company) {
                $btns = [];
                if (auth()->user()->hasPermission('edit company')) {
                    $btns[] = $this->editButton(route('general.settings', $company->setting->uid));
                }
                if (auth()->user()->hasPermission('delete company')) {
                    $btns[] = $this->deleteButton($company->uid, $company->setting->name);
                }
                return $this->actionButtons($btns);
            },
        ];
    }
}
