<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class OrderAttempt extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = ['payload' => 'array', 'validated_payload' => 'array'];
}
