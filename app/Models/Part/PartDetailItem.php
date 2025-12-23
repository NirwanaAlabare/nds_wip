<?php

namespace App\Models\Part;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SignalBit\BomJoItem;

class PartDetailItem extends Model
{
    use HasFactory;

    protected $table = 'part_detail_item';

    protected $guarded = [];

    /**
     * Get the part detail that own the item.
     */
    public function partDetail()
    {
        return $this->belongsTo(PartDetail::class, 'part_detail_id', 'id');
    }

    /**
     * Get the item.
     */
    public function bomJoItem()
    {
        return $this->belongsTo(BomJoItem::class, 'bom_jo_item_id', 'id');
    }
}
