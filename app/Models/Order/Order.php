<?php

namespace App\Models\Order;

use App\Models\OrderLine\OrderLine;
use App\Models\Shipping\Shipping;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const PENDING = 1;
    const IN_PROGRESS = 2;
    const SENT = 3;
    const DONE = 4;
    const FAILED = 5;

    protected $fillable = ['order_date', 'shipping_id', 'user_id', 'price', 'order_status_id'];

    public function shipping()
    {
        return $this->belongsTo(Shipping::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order_lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function order_status()
    {
        return $this->belongsTo(OrderStatus::class);
    }
}
