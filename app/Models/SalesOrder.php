<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranSalesOrder::class);
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'source');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }


}
