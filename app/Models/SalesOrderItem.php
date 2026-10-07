<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    protected $guarded = [];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function margin()
    {
        return $this->hasOne(OrderCost::class);
    }

    public function sub()
    {
        return $this->hasMany(SalesOrderSub::class);
    }

    public function invItem()
    {
         return $this->morphOne(InvoiceItem::class, 'source_item');
    }
}
