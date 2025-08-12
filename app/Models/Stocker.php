<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\InactiveStocker;

class Stocker extends Model
{
    use HasFactory;

    protected $table = 'stocker_input';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new InactiveStocker);
    }

    public static function lastId(): string
    {
        $max = self::selectRaw("MAX(CAST(SUBSTRING_INDEX(id_qr_stocker, '-', -1) AS UNSIGNED)) as max_id")->value('max_id');

        return $max;
    }

    /**
     * Get the master sb ws.
     */
    public function masterSbWs()
    {
        return $this->belongsTo(MasterSbWs::class, 'so_det_id', 'id_so_det');
    }

    /**
     * Get the part.
     */
    public function partDetail()
    {
        return $this->belongsTo(PartDetail::class, 'part_detail_id', 'id');
    }

    /**
     * Get the form.
     */
    public function formCut()
    {
        return $this->belongsTo(FormCutInput::class, 'form_cut_id', 'id');
    }

    /**
     * Get the form.
     */
    public function formReject()
    {
        return $this->belongsTo(FormCutReject::class, 'form_reject_id', 'id');
    }

    /**
     * Get the form.
     */
    public function formPiece()
    {
        return $this->belongsTo(FormCutPiece::class, 'form_piece_id', 'id');
    }

    /**
     * Get the stocker details.
     */
    public function stockerDetails()
    {
        return $this->hasMany(StockerDetail::class, 'stocker_id', 'id');
    }

    /**
     * Get the dc in stocker.
     */
    public function dcIn()
    {
        return $this->hasOne(DCIn::class, 'id_qr_stocker', 'id_qr_stocker');
    }

    public function secondaryInhouse()
    {
        return $this->hasOne(SecondaryInHouse::class, 'id_qr_stocker', 'id_qr_stocker');
    }

    public function secondaryIn()
    {
        return $this->hasOne(SecondaryIn::class, 'id_qr_stocker', 'id_qr_stocker');
    }

    /**
     * Get the stocker rack.
     */
    public function rackDetailStockers()
    {
        return $this->hasMany(RackDetailStocker::class, 'stocker_id', 'id_qr_stocker');
    }

    /**
     * Get the stocker trolley.
     */
    public function trolleyStockers()
    {
        return $this->hasMany(TrolleyStocker::class, 'stocker_id', 'id');
    }
}
