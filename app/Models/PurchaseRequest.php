<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $guarded = [];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function po()
    {
        return $this->hasOne(PurchaseOrder::class);
    }
}
