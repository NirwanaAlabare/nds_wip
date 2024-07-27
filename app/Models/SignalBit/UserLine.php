<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLine extends Model
{
    use HasFactory;

    protected $primaryKey = 'line_id';

    protected $connection = 'mysql_sb';

    protected $table = "userpassword";

    protected $guarded = [];

    public function masterPlans()
    {
        return $this->hasMany(MasterPlan::class, 'sewing_line', 'username');
    }

    public function users()
    {
        return $this->hasMany(UserSbWip::class, 'line_id', 'line_id');
    }
}
