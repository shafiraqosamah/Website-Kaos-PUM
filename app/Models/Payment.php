<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const DESTINATION_BANKS = [
        'bca' => [
            'label' => 'BCA',
            'account_number' => '123-456-7891',
            'account_name' => 'Keuangan',
        ],
        'bni' => [
            'label' => 'BNI',
            'account_number' => '123-456-7892',
            'account_name' => 'Keuangan',
        ],
        'bri' => [
            'label' => 'BRI',
            'account_number' => '123-456-7892',
            'account_name' => 'Keuangan',
        ],
        'mandiri' => [
            'label' => 'Mandiri',
            'account_number' => '123-456-7893',
            'account_name' => 'Keuangan',
        ],
    ];

    protected $fillable = [
        'order_id',
        'method',
        'invoice_number',
        'invoiced_at',
        'destination_bank',
        'sender_bank_name',
        'sender_account_name',
        'proof_path',
        'amount',
        'status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'invoiced_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public static function destinationBanks(): array
    {
        return self::DESTINATION_BANKS;
    }

    public function destinationBankDetails(): ?array
    {
        return self::DESTINATION_BANKS[$this->destination_bank] ?? null;
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
