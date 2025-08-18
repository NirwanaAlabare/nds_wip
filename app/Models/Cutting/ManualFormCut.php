<?php

namespace App\Models\Cutting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\ThisYearScope;

class ManualFormCut extends Model
{
    use HasFactory;

    protected $table = 'manual_form_cut_input';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ThisYearScope);
    }

    public function manualMarker()
    {
        return $this->belongsTo(ManualFormCutMarker::class, 'id_marker', 'kode');
    }

    public function alokasiMeja()
    {
        return $this->belongsTo(User::class, 'no_meja', 'id');
    }

    public function manualFormCutDetails()
    {
        return $this->hasMany(ManualFormCutDetail::class, 'no_form_cut_input', 'no_form');
    }
}
