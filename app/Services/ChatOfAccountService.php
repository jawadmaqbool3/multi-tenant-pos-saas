<?php

namespace App\Services;

use App\Repositories\AccountLevel1Repository;
use App\Repositories\AccountLevel2Repository;
use App\Repositories\AccountLevel3Repository;
use App\Repositories\AccountLevel4Repository;

class ChatOfAccountService
{
    protected $level1;
    protected $level2;
    protected $level3;
    protected $level4;

    public function __construct(
        AccountLevel1Repository $level1,
        AccountLevel2Repository $level2,
        AccountLevel3Repository $level3,
        AccountLevel4Repository $level4
    ) {
        $this->level1 = $level1;
        $this->level2 = $level2;
        $this->level3 = $level3;
        $this->level4 = $level4;
    }

    public function getAccounts()
    {
        return $this->level1->query()->with('level2.level3.level4')->filter()->get();
    }

    public function store($request)
    {
        switch ($request->level) {
            case 'level1':
                $parentAccount = $this->level1->query()->where('uid', $request->account)->first();
                $code = $this->level2->query()->where('parent_id', $parentAccount->id)->max('code');
                if ($code) {
                    $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                } else {
                    $code = $parentAccount->code + 1;
                }
                $this->level2->create([
                    'name' => $request->name,
                    'code' => $code,
                    'parent_id' => $parentAccount->id,
                    'company_id' => auth()->user()->company_id,
                    'status' => $request->status ?? 'active',
                ]);
                break;
            case 'level2':
                $parentAccount = $this->level2->query()->where('uid', $request->account)->first();
                $code = $this->level3->query()->where('parent_id', $parentAccount->id)->max('code');
                if ($code) {
                    $code = str_replace($parentAccount->code . '-', '', $code);
                } else {
                    $code = 0;
                }
                $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                $code = $parentAccount->code . '-' . $code;
                $this->level3->create([
                    'name' => $request->name,
                    'code' => $code,
                    'parent_id' => $parentAccount->id,
                    'company_id' => auth()->user()->company_id,
                    'status' => $request->status ?? 'active',
                ]);
                break;
            case 'level3':
                $parentAccount = $this->level3->query()->where('uid', $request->account)->first();
                $code = $this->level4->query()->where('parent_id', $parentAccount->id)->max('code');
                if ($code) {
                    $code = str_replace($parentAccount->code . '-', '', $code);
                } else {
                    $code = 0;
                }
                $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                $code = $parentAccount->code . '-' . $code;
                $this->level4->create([
                    'name' => $request->name,
                    'code' => $code,
                    'parent_id' => $parentAccount->id,
                    'company_id' => auth()->user()->company_id,
                    'status' => $request->status ?? 'active',
                ]);
                break;
            default:
                $code = $this->level1->query()->filter()->max('code');
                if ($code) {
                    $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                } else {
                    $code = 0;
                }
                $endCode = $code + 99;
                $code = str_pad($code, 3, 0, STR_PAD_LEFT);
                $endCode = str_pad($endCode, 3, 0, STR_PAD_LEFT);
                $this->level2->create([
                    'name' => $request->name,
                    'start_code' => $code,
                    'end_code' => $endCode,
                    'company_id' => auth()->user()->company_id,
                    'status' => $request->status ?? 'active',
                ]);
        }
    }

    public function update($request, $account)
    {
        $repository = $this->getRepositoryByLevel($request->level);
        $repository->query()->where('uid', $account)->update([
            'name' => $request->name,
            'status' => $request->status ?? 'active',
        ]);
    }

    public function delete($request, $account)
    {
        $repository = $this->getRepositoryByLevel($request->level);
        $repository->query()->where('uid', $account)->delete();
    }

    protected function getRepositoryByLevel($level)
    {
        switch ($level) {
            case 'level1':
                return $this->level1;
            case 'level2':
                return $this->level2;
            case 'level3':
                return $this->level3;
            case 'level4':
                return $this->level4;
            default:
                throw new \InvalidArgumentException('Invalid account level');
        }
    }
}
