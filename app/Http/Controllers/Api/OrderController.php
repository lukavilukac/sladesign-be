<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Requests\CreateOrderRequest;
use App\Models\Order\Order;
use App\Models\Order\OrderService;
use Illuminate\Http\Request;

class OrderController
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @throws \Exception
     */
    public function create(CreateOrderRequest $request)
    {
        $data = $request->validated();
        $data['order_status_id'] = Order::PENDING;

        $this->orderService->createOrder($data);

        return response()->json([], 201);
    }

    public function update($id, Request $request)
    {

    }
}
