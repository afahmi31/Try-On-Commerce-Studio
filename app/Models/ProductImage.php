<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'source_type',
        'image_type',
        'is_primary',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'bool',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->source_type === 'external') {
            return $this->path;
        }

        return asset('storage/'.$this->path);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}