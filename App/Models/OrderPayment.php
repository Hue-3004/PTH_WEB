<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class OrderPayment extends Model {
    protected $table = 'payments';
    
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'payment_status',
        'payment_date',
        'transaction_id',
        'created_at',
        'updated_at'
    ];

    // Nếu muốn, có thể thêm các accessor/mutator hoặc quan hệ ở đây

    public function order() {
        return $this->belongsTo(Order::class);
    }
}