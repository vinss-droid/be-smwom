<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = [
        'operator_id',
        'no_wo',
        'product_name',
        'date_work_order',
        'deadline',
        'qty_order',
        'qty_pending',
        'qty_inProgress',
        'qty_completed',
        'qty_canceled',
        'status',
        'note',
        'finished_at',
    ];
}
