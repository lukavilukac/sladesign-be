<?php

namespace App\Models\Order;

use App\Mail\OrderConfirmation;
use App\Mail\OrderToAdmin;
use App\Models\OrderLine\OrderLine;
use App\Models\Shipping\Shipping;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    public function getAllOrdersForAdmin()
    {
        return Order::orderBy('id', 'desc')
            ->with('order_lines.product.product_image','order_lines.modifiers', 'user', 'shipping')->get()
            ->mapWithKeys(function ($order) {
                return [$order->id => $order];
            });
    }

    public function getAllOrderStatusesForAdmin()
    {
        $orderStatuses = OrderStatus::with('orders')->get()
            ->mapWithKeys(function ($orderStatus) {
                return [$orderStatus->id => $orderStatus];
        });
        $orderStatuses->map(function ($status) {// frontend requres like this
            $status->orderIds = $status->orders->pluck('id')->map(function ($id) {
                return (string)$id;
            })->values();
            unset($status->orders);
        });

        return $orderStatuses;
    }

    public function getOrderById($id)
    {
        return Order::query()->where('id', $id)
            ->with('order_lines.product.product_image','order_lines.modifiers', 'user', 'shipping')
            ->first();
    }

    /**
     * @throws \Exception
     */
    public function createOrder(array $data): void
    {
        try {
            $phoneNumber = $data['country_code'] . $data['phone_number'];
            DB::beginTransaction();
            $user = $this->upsertUser($data['email_address'], $data['first_name'], $data['last_name'], $data['country_name'], $data['city'], $data['address'], $phoneNumber, $data['apartment']);
            //create or update shipping
            $shipping = $this->upsertShipping($data['country_name'], $data['city'], $data['address'], $phoneNumber, $data['apartment']);

            //create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_date' => now(),//maybe remove
                'order_status_id' => $data['order_status_id'],
                'price' => $data['price'],
                'shipping_id' => $shipping->id,
            ]);

            foreach ($data['order_lines'] as $orderLineData) {
                $orderLine = OrderLine::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $orderLineData['product_id'],
                    'quantity' => $orderLineData['quantity'],
                    'price' => $orderLineData['price']//todo - calculate price based on modifiers(check)
                ]);

                $orderLine->modifiers()->attach($orderLineData['modifier_ids']);
            }
            DB::commit();
            //send email to slavica and me!
            Mail::to(config('mail.admin_address'))->send(new OrderToAdmin($order, $user));
            //send email to user!
            Mail::to($user->email)->send(new OrderConfirmation($order, $user));
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Order creation Error! ----- Payload: ' . json_encode($data));
            //send email to me!
            throw $exception;
        }
    }

    /**
     * @return User
     */
    public function upsertUser(string $email, string $firstName, string $lastName, string $country, string $city, string $address, string $phoneNumber, ?string $apartment): Model
    {
        return User::query()->updateOrCreate([
               'email' => $email
            ],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'country' => $country,
                'city' => $city,
                'address' => $address,
                'phone_number' => $phoneNumber,
                'apartment' => (string)$apartment,
            ]
        );
    }

    public function upsertShipping(string $country, string $city, string $address, string $phoneNumber, ?string $apartment): Model
    {
        return Shipping::query()->updateOrCreate([ // maybe optimize this one
            'country' => $country,
            'city' => $city,
            'address' => $address,
            'phone_number' => $phoneNumber,
            'apartment' => (string)$apartment
        ]);
    }

    public function updateOrder($id, $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }

    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
    }
}
