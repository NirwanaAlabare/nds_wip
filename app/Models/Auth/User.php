<?php

namespace App\Models\Auth;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'department', 'password', 'password_text', 'type', 'cutting_unlocker'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get all of the roles for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function roles()
    {
        return $this->hasManyThrough(
            Role::class,
            UserRole::class,
            'user_id', // Foreign key on the user role table...
            'id', // Foreign key on the role table...
            'id', // Local key on the user table...
            'role_id' // Local key on the user role table...
        );
    }

    /**
     * Get all of the user role for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRole()
    {
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    /**
     * Get all of the connections for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function connectionList()
    {
        return $this->hasManyThrough(
            ConnectionList::class,
            UserConnection::class,
            'user_id', // Foreign key on the user role table...
            'id', // Foreign key on the role table...
            'id', // Local key on the user table...
            'connection_id' // Local key on the user role table...
        );
    }

    /**
     * Get all of the user connection for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userConnection()
    {
        return $this->hasMany(UserConnection::class, 'user_id', 'id');
    }
}
