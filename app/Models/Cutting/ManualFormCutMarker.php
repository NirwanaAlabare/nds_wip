<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFormCutMarker extends Model
{
    use HasFactory;

    protected $table = 'manual_form_cut_input_marker';

    protected $guarded = [];
}
