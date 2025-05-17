<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    protected $fillable = ['name', 'slug', 'image', 'sort_oder', 'description', 'status', 'created_at', 'updated_at'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}