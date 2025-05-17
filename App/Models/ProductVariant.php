<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model {
    protected $table = 'product_variants'; 
    protected $fillable = ['product_id', 'size', 'color', 'sku', 'stock_quantity', 'price_old', 'price_new'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }
    
} 