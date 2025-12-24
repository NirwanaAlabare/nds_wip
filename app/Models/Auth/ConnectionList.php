<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectionList extends Model
{
    use HasFactory;

    protected $table = 'connection_list';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function userConnections()
    {
        return $this->hasMany(UserConnection::class, 'connection_id', 'id');
    }
}
