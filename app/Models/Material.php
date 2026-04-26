<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'name',
        'slug',
        'base_price',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
