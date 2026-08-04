<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'name',
        'slug',
        'base_price',
        'sort_order',
        'is_active',
        'title',
        'description',
        'image_path',
        'tags',
        'suitable_for',
        'design_application',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'is_active' => 'boolean',
            'tags' => 'array',
            'suitable_for' => 'array',
            'design_application' => 'array',
        ];
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'material_colors')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }
}
