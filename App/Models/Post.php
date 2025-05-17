<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'author_id',
        'title',
        'short_title',
        'content',
        'image',
        'status',
        'views',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'status' => 'integer',
        'views' => 'integer',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];

    // Relationship with User model (assuming author is a User)
    public function user()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
