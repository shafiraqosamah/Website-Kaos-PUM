<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemSize extends Model
{
    protected $fillable = [
        'order_id',
        'size_name',
        'qty',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
