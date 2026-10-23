<?php

namespace App\Models\OrderLine;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderLineModifier extends Pivot
{
    protected $table = 'modifier_order_line';

    public $timestamps = false;

    public function orderLine()
    {
        return $this->belongsTo(OrderLine::class);
    }
}
