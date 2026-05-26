<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Seller extends Model
{
    protected $fillable = [
        'owner_user_id',
        'store_name',
        'slug',
        'seo_title',
        'seo_description',
        'seo_logo_url',
        'status',
        'ui_locale',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function aiSetting(): HasOne
    {
        return $this->hasOne(SellerAiSetting::class);
    }
}
