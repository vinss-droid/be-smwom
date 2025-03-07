<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderProgress extends Model
{
    protected $fillable = ['work_order_id', 'status', 'quantity', 'notes'];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
