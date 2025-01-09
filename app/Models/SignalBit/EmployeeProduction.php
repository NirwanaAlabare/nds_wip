<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProduction extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_employee';

    protected $guarded = [];

    public $timestamps = true;

    public function leaderLines()
    {
        return $this->hasMany(LeaderLine::class, 'employee_id', 'enroll_id');
    }
}
