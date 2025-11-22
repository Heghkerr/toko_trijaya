<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/profile-photos/'.$this->photo);
        }
        return asset('images/default-profile.png');
    }
}
