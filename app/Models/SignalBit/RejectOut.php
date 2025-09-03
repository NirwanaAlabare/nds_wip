<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class RejectOut extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_reject_out';

    protected $fillable = [
        'id',
        'reject_out_id',
        'tanggal',
        'no_transaksi',
        'tujuan',
        'created_by',
        'created_by_username',
        'created_at',
        'updated_at',
    ];

    public static function lastId(): string
    {
        $prefix = "R".date("dmy");
        $max = DB::table("output_reject_out")->selectRaw("MAX(CAST(SUBSTRING_INDEX(no_transaksi, '/', -1) AS UNSIGNED)) as max_id")->whereRaw("SUBSTRING_INDEX(no_transaksi, '/', 1) LIKE '%" . $prefix . "%'")->value('max_id');

        return $max ? $max : 0;
    }

    public function rejectOutDetail()
    {
        return $this->hasMany(RejectOutDetail::class, 'reject_out_id', 'id');
    }
}
