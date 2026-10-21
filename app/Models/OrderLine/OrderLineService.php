<?php

namespace App\Models\OrderLine;

class OrderLineService
{
    public function getAllOrderLines()
    {
        return OrderLine::all();
    }

    public function getOrderLineById($id)
    {
        return OrderLine::findOrFail($id);
    }

    public function createOrderLine($data)
    {
        $orderLine = OrderLine::create([
            'order_id' => $data['order_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'price' => $data['price']//todo - calculate price
        ]);

        $modifierIds = data_get($data, 'modifier_ids', []);

        if (is_array($modifierIds) && $modifierIds !== []) {
            $orderLine->modifiers()->sync($modifierIds);
        }

        return $orderLine;
    }

    public function updateOrderLine($id, $data)
    {
        $orderLine = OrderLine::findOrFail($id);
        $orderLine->update($data);
        return $orderLine;
    }

    public function deleteOrderLine($id)
    {
        $orderLine = OrderLine::findOrFail($id);
        $orderLine->delete();
    }
}
