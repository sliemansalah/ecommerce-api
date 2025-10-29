<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * علاقة: المستخدم يملك عدة مقالات (One To Many)
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}