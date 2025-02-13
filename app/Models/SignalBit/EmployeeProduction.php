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

    public function employeeLines()
    {
        return $this->hasMany(EmployeeLine::class, 'employee_id', 'enroll_id');
    }
}
