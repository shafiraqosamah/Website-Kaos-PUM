<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Color extends Model
{
    protected $table = 'colors';

    protected $fillable = [
        'name',
        'slug',
        'hex_code',
        'gradient_css',
        'swatch_image_path',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_colors')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
