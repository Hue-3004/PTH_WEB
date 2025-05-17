<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $table = 'orders';
    
    protected $fillable = [
        'user_id',
        'order_code',
        'order_date',
        'status',
        'total_amount',
        'shipping_address_id',
        'billing_address_id',
        'created_at',
        'updated_at'
    ];

    // Relationship với User
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Relationship với OrderItems
    public function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
    
    // Relationship với địa chỉ giao hàng
    public function shippingAddress() {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    // Relationship
    // public function billingAddress() {
    //     return $this->belongsTo(OrderPayment::class, 'billing_address_id');
    // }

    public function payment() {
        return $this->belongsTo(OrderPayment::class, 'billing_address_id', 'id');
    }

    // Lấy trạng thái đơn hàng dạng text
    public function getStatusTextAttribute() {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy'
        ];
        return $statusMap[$this->status] ?? $this->status;
    }

    // Format ngày đặt hàng
    public function getFormattedOrderDateAttribute() {
        return date('d/m/Y H:i', strtotime($this->order_date));
    }

    // Format tổng tiền
    public function getFormattedTotalAmountAttribute() {
        return number_format($this->total_amount, 0, ',', '.') . ' VNĐ';
    }
}
