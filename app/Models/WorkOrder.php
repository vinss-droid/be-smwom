<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $fillable = ['work_order_number', 'product_name', 'quantity', 'deadline', 'status', 'assigned_operator_id', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedOperator()
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }

    public function progress()
    {
        return $this->hasMany(WorkOrderProgress::class);
    }
}
