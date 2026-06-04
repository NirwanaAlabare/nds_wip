<?php

namespace App\Models\Marker;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MasterSbWs;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class MarkerDetail extends Model
{
    use HasFactory, LogsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    protected $table = 'marker_input_detail';

    protected $guarded = [];

    /**
     * Get the marker that own the details.
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $route = request()->route();
        $activity->properties = $activity->properties->merge([
            'route'  => $route ? $route->getName() : null,
            'action' => $route ? $route->getActionMethod() : null,
            'url'    => request()->fullUrl(),
        ]);
    }

    public function marker()
    {
        return $this->belongsTo(Marker::class, 'marker_id', 'id');
    }

    public function masterSbWs()
    {
        return $this->belongsTo(MasterSbWs::class, 'so_det_id', 'id_so_det');
    }
}
