<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];


    public function productVariantPrices(): HasMany
    {
        return $this->hasMany(ProductVariantPrice::class);
    }

}
