<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLineTmp extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_employee_line_tmp';

    protected $guarded = [];

    public $timestamps = true;

    public function line()
    {
        return $this->belongsTo(UserLine::class, 'line_id', 'line_id');
    }
}
