<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function User() 
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
    return $this->belongsToMany(User::class, 'liked_posts', 'post_id', 'user_id')->withTimestamps();
    }


}
