<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    protected $table = 'access';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function roleAccess()
    {
        return $this->hasMany(RoleAccess::class, 'access_id', 'id');
    }
}
