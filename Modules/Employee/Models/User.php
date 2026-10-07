<?php

namespace Modules\Employee\Models;

use App\Core\Helper;
use Modules\Employee\Http\Traits\EmployeeTrait;
use Modules\RBAC\Http\Traits\RoleTrait;
use Modules\Employee\Http\Traits\UserDataTableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        UserDataTableTrait,
        EmployeeTrait,
        RoleTrait;
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

    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $query;
        } else {
            return $query->where('company_id', auth()->user()->company_id)->where('id', '!=',  auth()->user()->id);
        }
    }








    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function setting()
    {
        if ($this->company_id) {
            return Setting::where('company_id', $this->company_id)->first();
        } else {
            return Setting::where('company_id', null)->first();
        }
    }
}
