<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    
    protected $fillable = [
        'user_id',
        'address_type',
        'street',
        'city',
        'state',
        'postal_code',
        'country',
        'created_at',
        'updated_at'
    ];

    // Định nghĩa các giá trị mặc định cho address_type
    const TYPE_SHIPPING = 'shipping';
    const TYPE_BILLING = 'billing';

    // Relationship với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope để lấy địa chỉ giao hàng
    public function scopeShipping($query)
    {
        return $query->where('address_type', self::TYPE_SHIPPING);
    }

    // Scope để lấy địa chỉ thanh toán
    public function scopeBilling($query)
    {
        return $query->where('address_type', self::TYPE_BILLING);
    }

    // Accessor để lấy địa chỉ đầy đủ
    public function getFullAddressAttribute()
    {
        $address = $this->street;
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        if ($this->postal_code) {
            $address .= ', ' . $this->postal_code;
        }
        if ($this->country) {
            $address .= ', ' . $this->country;
        }
        return $address;
    }

    // Mutator để đảm bảo address_type luôn là một trong hai giá trị cho phép
    public function setAddressTypeAttribute($value)
    {
        $this->attributes['address_type'] = in_array($value, [self::TYPE_SHIPPING, self::TYPE_BILLING]) 
            ? $value 
            : self::TYPE_SHIPPING;
    }

    // Tự động set created_at và updated_at
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
