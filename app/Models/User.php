<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    // إضافة الـ roles و permissions في الـ response
    protected $appends = ['role_names', 'permission_names'];
    
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name');
    }
    
    public function getPermissionNamesAttribute()
    {
        return $this->getAllPermissions()->pluck('name');
    }
}