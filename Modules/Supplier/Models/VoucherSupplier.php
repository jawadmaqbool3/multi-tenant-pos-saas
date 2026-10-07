<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherSupplier extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;
    public $autoincrement = false;

    
}
