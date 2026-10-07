<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $guarded = [];

    // public function poItem()
    // {
    //     return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    // }

    public function sourceItem()
    {
        return $this->morphTo(); // PO atau SO
    }

}
