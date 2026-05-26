<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryOnSession extends Model
{
    protected $table = 'tryon_sessions';

    protected $fillable = [
        'seller_id',
        'product_id',
        'customer_photo_path',
        'status',
        'quality_mode',
        'source_channel',
        'ip_address',
        'user_agent',
        'device_id',
        'provider_name',
        'provider_model',
        'provider_job_id',
        'result_path',
        'error_message',
        'token_cost',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
