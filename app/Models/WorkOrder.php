<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $fillable = ['work_order_number', 'product_name', 'quantity', 'deadline', 'status', 'assigned_operator_id', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this
            ->belongsTo(User::class, 'created_by')
            ->select(['id', 'name']);
    }

    public function assignedOperator(): BelongsTo
    {
        return $this
            ->belongsTo(User::class, 'assigned_operator_id')
            ->select(['id', 'name']);
    }


    public function progress(): HasMany
    {
        return $this
            ->hasMany(WorkOrderProgress::class);
    }

    public static function generateWorkOrderNumber(): string
    {
        $today = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? ((int) substr($lastOrder->work_order_number, -3)) + 1 : 1;

        return sprintf('WO-%s-%03d', $today, $sequence);
    }
}
