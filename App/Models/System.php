<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $table = 'systems';
    protected $fillable = ['logo', 'site_name', 'hotline', 'email', 'address', 'created_at', 'updated_at'];

}