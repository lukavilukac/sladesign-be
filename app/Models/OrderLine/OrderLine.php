<?php

namespace App\Models\OrderLine;

use App\Models\Modifier\Modifier;
use App\Models\Order\Order;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    protected $fillable = ['product_id', 'order_id', 'quantity', 'price'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'modifier_order_line');
    }
}
