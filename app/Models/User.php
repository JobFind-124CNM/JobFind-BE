<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Support\UserStatus;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

     protected $fillable = [
        'username',
        'email',
        'password',
        'status',
        'is_deleted',
        'avatar',
        'cv',
        'experience',
        'gender',
        'phone',
        'google_id',
        'facebook_id',
    ];

    public function setStatusAttribute($value)
    {
        if (!UserStatus::isValid($value)) {
            throw new \InvalidArgumentException("Invalid user status: {$value}");
        }

        $this->attributes['status'] = $value;
    }

    public function getStatusLabelAttribute()
    {
        return UserStatus::getLabel($this->status);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

     public function posts()
    {
        return $this->belongsToMany(Post::class, 'user_post', 'user_id', 'post_id')
                    ->withPivot('cv', 'cover_letter', 'status', 'subject')
                    ->withTimestamps();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

     public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
