<?php

namespace Modules\Supplier\Models;

use App\Models\UIDModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends UIDModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public function scopeFilter($query)
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $query;
        } else {
            return $query->where('company_id', auth()->user()->company_id);
        }
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id', 'id');
    }

    public function voucherSuppliers(): HasMany
    {
        return $this->hasMany(VoucherSupplier::class, 'supplier_id', 'id');
    }
}
