<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function userRole()
    {
        return $this->hasMany(UserRole::class, 'role_id', 'id');
    }

    public function accesses()
    {
        return $this->hasManyThrough(
            Access::class,
            RoleAccess::class,
            'role_id', // Foreign key on the role access table...
            'id', // Foreign key on the access table...
            'id', // Local key on the role table...
            'access_id' // Local key on the role access table...
        );
    }
}
