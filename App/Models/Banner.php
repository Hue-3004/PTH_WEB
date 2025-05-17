<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';
    protected $fillable = ['name', 'title', 'image', 'link', 'status', 'created_at', 'updated_at'];
}