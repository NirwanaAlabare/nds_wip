<?php

namespace App\Models\Dc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stocker\Stocker;
use Spatie\Activitylog\Traits\LogsActivity;


class DCIn extends Model
{
    use HasFactory, LogsActivity;

    protected $table = "dc_in_input";

    protected $guarded = [];

    //only the `deleted` event will get logged automatically
    protected static $recordEvents = ['updated', 'deleted'];

    protected static $logAttributes = ['*'];


    /**
     * Get the stocker dc in.
     */
    public function stocker()
    {
        return $this->hasOne(Stocker::class, 'id_qr_stocker', 'id_qr_stocker');
    }

    /**
     * Get the secondary inhouse stocker.
     */
    public function secondaryInHouse()
    {
        return $this->hasOne(SecondaryInHouse::class, 'id_qr_stocker', 'id_qr_stocker');
    }

    /**
     * Get the secondary in stocker.
     */
    public function secondaryIn()
    {
        return $this->hasOne(SecondaryIn::class, 'id_qr_stocker', 'id_qr_stocker');
    }
}
