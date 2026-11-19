<?php

namespace App\Models\Stocker;

use App\Models\SignalBit\SoDet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\InactiveStocker;
use App\Models\MasterSbWs;
use App\Models\Part\PartDetail;
use App\Models\Cutting\FormCutInput;
use App\Models\Cutting\FormCutReject;
use App\Models\Cutting\FormCutPiece;
use App\Models\Dc\DCIn;
use App\Models\Dc\SecondaryInHouse;
use App\Models\Dc\SecondaryIn;
use App\Models\Dc\RackDetailStocker;
use App\Models\Dc\TrolleyStocker;
use Spatie\Activitylog\Traits\LogsActivity;
use DB;

class Stocker extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'stocker_input';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new InactiveStocker);
    }

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    protected static $logAttributes = ['*'];

    public static function lastId(): string
    {
        $max = DB::table("stocker_input")->selectRaw("MAX(CAST(SUBSTRING_INDEX(id_qr_stocker, '-', -1) AS UNSIGNED)) as max_id")->value('max_id');

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

    /**
     * Qty stocker setelah penyesuaian reject & replace dari DC In / Secondary In.
     */
    public function getQtyAdjustedAttribute()
    {
        $qty = $this->qty_ply_mod > 0 ? $this->qty_ply_mod : $this->qty_ply;

        foreach ([$this->dcIn, $this->secondaryInhouse] as $adjustment) {
            if ($adjustment) {
                $qty += $adjustment->qty_replace - $adjustment->qty_reject;
            }
        }

        if ($this->secondaryIn) {
            $qty += $this->secondaryIn->qty_replace - $this->secondaryIn->qty_reject;

            // Penyesuaian lanjutan secondary in tersimpan di secondary_in_update
            $updates = $this->secondaryIn->secondaryInUpdate;
            $qty += $updates->sum("replace") - $updates->sum("reject");
        }

        return $qty;
    }

    /**
     * Semua secondary in stocker (satu baris per urutan pada multi secondary).
     */
    public function secondaryIns()
    {
        return $this->hasMany(SecondaryIn::class, 'id_qr_stocker', 'id_qr_stocker');
    }

    /**
     * Relasi yang dibutuhkan getCurrentQtyAttribute, untuk eager loading.
     */
    public const CURRENT_QTY_RELATIONS = [
        'partDetail.secondaries.secondary',
        'secondaryIns.secondaryInUpdate',
        'dcIn',
        'secondaryIn.secondaryInUpdate',
        'secondaryInhouse',
    ];

    /**
     * Qty stocker saat ini (posisi proses terakhir), dipakai untuk tampilan & alokasi loading line.
     *
     * - Multi secondary, proses terakhir SECONDARY     : secondary in urutan terakhir (qty_in - reject + replace update)
     * - Multi secondary, proses terakhir NON SECONDARY : dc in (qty_awal - reject + replace)
     * - Selain itu / data belum ada / hasil < 1        : qty_adjusted
     */
    public function getCurrentQtyAttribute()
    {
        $qty = null;

        $secondaries = $this->partDetail ? $this->partDetail->secondaries : null;
        $lastSecondary = $secondaries ? $secondaries->sortByDesc('urutan')->first() : null;

        if ($lastSecondary && $lastSecondary->secondary) {
            if ($lastSecondary->secondary->tujuan != 'NON SECONDARY') {
                $secondaryIn = $this->secondaryIns->firstWhere('urutan', $lastSecondary->urutan);

                if ($secondaryIn) {
                    $updates = $secondaryIn->secondaryInUpdate;
                    $qty = $secondaryIn->qty_in - $updates->sum('reject') + $updates->sum('replace');
                }
            } else if ($this->dcIn) {
                $qty = $this->dcIn->qty_awal - $this->dcIn->qty_reject + $this->dcIn->qty_replace;
            }
        }

        // Fallback, sekaligus mencegah qty nol pada stocker reject
        if ($qty === null || $qty < 1) {
            $qty = $this->qty_adjusted;
        }

        return $qty;
    }
}
