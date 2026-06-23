<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCeisaCredential extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'master_ceisa_credentials';

    protected $fillable = [
        'user_id',
        'ceisa_username',
        'ceisa_password',
        'ceisa_api_key',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\Auth\User::class, 'user_id', 'id');
    }
}
