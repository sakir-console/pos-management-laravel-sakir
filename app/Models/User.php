<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function user_info()
    {
        return $this->belongsTo(UserInfo::class);
    }

    public function followedUsers()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'follower_id', 'user_id')->withTimestamps();
    }

    public function followedVendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_followers', 'follower_id', 'vendor_id')->withTimestamps();
    }


}