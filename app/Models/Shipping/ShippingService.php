<?php

namespace App\Models\Shipping;

class ShippingService
{
    public function getAllShippings()
    {
        return Shipping::all();
    }

    public function getShippingById($id)
    {
        return Shipping::findOrFail($id);
    }

    public function createShipping($data)
    {
        return Shipping::create($data);
    }

    public function updateShipping($id, $data)
    {
        $shipping = Shipping::findOrFail($id);
        $shipping->update($data);
        return $shipping;
    }

    public function deleteShipping($id)
    {
        $shipping = Shipping::findOrFail($id);
        $shipping->delete();
    }
}
