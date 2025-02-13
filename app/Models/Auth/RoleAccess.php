<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAccess extends Model
{
    use HasFactory;

    protected $table = 'role_access';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function access()
    {
        return $this->belongsTo(Access::class, 'access_id', 'id');
    }

}
