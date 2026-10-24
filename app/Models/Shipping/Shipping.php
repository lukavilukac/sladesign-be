<?php

namespace App\Models\Shipping;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;


class Shipping extends Model
{
    protected $fillable = ['city', 'street', 'country', 'address', 'phone_number', 'apartment', 'postal_code'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
