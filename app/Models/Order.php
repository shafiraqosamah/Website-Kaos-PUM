<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_code',
        'customer_name',
        'product_name',
        'total_pcs',
        'fabric',
        'dominant_color',
        'design_path',
        'estimated_finish_date',
        'unit_price',
        'subtotal',
        'payment_type',
        'dp_amount',
        'remaining_amount',
        'payment_status',
        'order_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_finish_date' => 'date',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(OrderItemSize::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    public function productionSteps(): HasMany
    {
        return $this->hasMany(ProductionStep::class)->orderBy('step_order');
    }

    public function isSettlementRequired(): bool
    {
        return $this->remaining_amount > 0;
    }
}
