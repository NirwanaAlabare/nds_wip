<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConnection extends Model
{
    use HasFactory;

    protected $table = 'user_connection';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function connectionList()
    {
        return $this->belongsTo(ConnectionList::class, 'connection_id', 'id');
    }
}
