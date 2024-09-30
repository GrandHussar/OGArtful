<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username', // Add 'username' to the list of fillable attributes
        'email',
        'google_id', // Add 'google_id' to the list of fillable attributes
        'password',
        'license_id',
        'roles',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function Posts()
    {
        return $this->hasMany(Post::class);
    }

    public function likes()
    {
    return $this->belongsToMany(Post::class, 'liked_posts', 'post_id', 'user_id')->withTimestamps();
    }


    public function hasLiked(Post $post)
    {
        return $this->likes()->where('post_id', $post->id)->exists();
    }

    // In User.php model
    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    

}
