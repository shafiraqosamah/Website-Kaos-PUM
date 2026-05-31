<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'invoice_number',
        'invoiced_at',
        'amount',
        'status',
        'verified_by',
        'verified_at',
        'notes',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_status',
        'midtrans_payment_type',
        'midtrans_fraud_status',
        'midtrans_response',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'invoiced_at' => 'datetime',
            'verified_at' => 'datetime',
            'midtrans_response' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
