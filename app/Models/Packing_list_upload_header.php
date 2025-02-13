<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packing_list_upload_header extends Model
{
    use HasFactory;

    protected $table = 'packing_master_upload_packing_list_header_horizontal';

    protected $guarded = [];
}
