<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
    protected $table = 'order_items';
    
    protected $fillable = [
        'order_id',
        'variant_id',
        'quantity',
        'unit_price',
        'created_at',
        'updated_at'
    ];

    // Relationship với Order
    public function order() {
        return $this->belongsTo(Order::class);
    }

    // Relationship với ProductVariant
    public function variant() {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Tính tổng tiền cho item
    public function getSubtotalAttribute() {
        return $this->quantity * $this->unit_price;
    }

    // Format đơn giá
    public function getFormattedUnitPriceAttribute() {
        return number_format($this->unit_price, 0, ',', '.') . ' VNĐ';
    }

    // Format tổng tiền
    public function getFormattedSubtotalAttribute() {
        return number_format($this->subtotal, 0, ',', '.') . ' VNĐ';
    }
} 