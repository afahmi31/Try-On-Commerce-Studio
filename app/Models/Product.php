<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'seller_id',
        'name',
        'slug',
        'sku',
        'category',
        'ai_prompt',
        'ai_category',
        'ai_garment_photo_type',
        'ai_segmentation_free',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'ai_segmentation_free' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
