<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDefect extends Model
{
    use HasFactory;

    protected $primaryKey = 'line_id';

    protected $connection = 'mysql_sb';

    protected $table = "userpassword";

    protected $guarded = [];

    public function defects()
    {
        return $this->hasMany(DefectInOut::class, 'type', 'Groupp');
    }
}
