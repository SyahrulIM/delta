<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];

    public function items() {
        return $this->hasMany(InvoiceItem::class);
    }

    public function piutangs() {
        return $this->hasMany(Piutang::class);
    }

    public function source()
    {
        return $this->morphTo(); // PO atau SO
    }

    public function poPayments()
    {
        return $this->hasMany(PurchaseOrderPayment::class);
    }


    // public function po() {
    //     return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    // }

}
