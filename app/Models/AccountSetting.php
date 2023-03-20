<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AccountSetting extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];

    public function scopeFilter($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }


    public function account(): HasOne
    {
        $account = AccountLevel4::class;
        switch ($this->level) {
            case 'level3':
                $account = AccountLevel3::class;
                break;
            case 'level2':
                $account = AccountLevel2::class;
                break;
            case 'level1':
                $account = AccountLevel1::class;
                break;
        }
        return $this->hasOne($account, 'id', 'account_id');
    }
}
