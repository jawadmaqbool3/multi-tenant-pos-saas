<?php

namespace App\Models;

use App\Core\Helper;
use Illuminate\Database\Eloquent\Model;

class UIDModel extends Model
{
    protected $primaryKey = 'uid';
    public $incrementing = false;
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uid = Helper::_uid();
        });
    }
    public function resetKey()
    {
        $this->primaryKey = 'id';
        $this->incrementing = true;
    }
}
