<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order\Order;
use App\Models\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class OrderController
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        return Inertia::render('Admin/Orders/Orders');
    }

    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);

        return Inertia::render('Admin/Orders/Order', ['order' => $order]);
    }

    public function tableOrders()
    {
        return Inertia::render('Admin/OrdersTable/TableOrders');
    }

    public function all()
    {
        $orders = $this->orderService->getAllOrdersForAdmin();
        $statuses = $this->orderService->getAllOrderStatusesForAdmin();

        $response = [
            'json' => [
                'orders' => $orders,
                'columns' => $statuses,
                'columnOrder' => $statuses->pluck('id')->values()
            ]
        ];


        return response()->json($response);
    }

    public function updateStatus(Request $request, $orderId)
    {
        $data = $request->validate([
            'order_status_id' => 'required',
            'redirectRoute' => 'nullable'
        ]);

        $this->orderService->updateOrder($orderId, Arr::only($data, ['order_status_id']));

        session()->flash('message', 'Order ' . $orderId . ' updated!');

        $redirectUrl = data_get($data, 'redirectRoute', 'admin.orders.index');

        return to_route($redirectUrl);
    }
}
