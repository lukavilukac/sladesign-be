<?php

namespace App\Models\Order;

use App\Mail\OrderConfirmation;
use App\Mail\OrderToAdmin;
use App\Models\Shipping\Shipping;
use App\Models\User;
use App\Services\OrderAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderService
{
    public function getAllOrdersForAdmin()
    {
        return Order::orderBy('id', 'desc')
            ->with('order_lines.product.product_image', 'order_lines.modifiers', 'user', 'shipping')->get()
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
                return (string) $id;
            })->values();
            unset($status->orders);
        });

        return $orderStatuses;
    }

    public function getOrderById($id)
    {
        return Order::query()->where('id', $id)
            ->with('order_lines.product.product_image', 'order_lines.modifiers', 'user', 'shipping')
            ->first();
    }

    public function createOrder(array $data): Order
    {
        $audit = app(OrderAudit::class);
        $previousAttempt = $audit->attempt;
        $attempt = $audit->attempt ?? $audit->begin($data, 'service');
        $phase = 'validation_complete';
        $completed = [];

        try {
            $attempt->update(['validated_payload' => $audit->payload($data), 'status' => 'processing']);
            $audit->record('validation_passed');
            try {
                $order = DB::transaction(function () use ($data, $attempt, $audit, &$phase, &$completed) {
                    $phase = 'customer';
                    $phoneNumber = $data['country_code'].$data['phone_number'];
                    $user = $this->upsertUser($data['email_address'], $data['first_name'], $data['last_name'],
                        $data['country_name'], $data['city'], $data['address'], $phoneNumber, $data['apartment']);
                    $completed[] = $phase;
                    $audit->record('customer_prepared', properties: ['customer_id' => $user->id]);

                    $phase = 'shipping';
                    $shipping = $this->upsertShipping($data['country_name'], $data['city'], $data['address'], $phoneNumber, $data['apartment']);
                    $completed[] = $phase;
                    $audit->record('shipping_prepared', properties: ['shipping' => $shipping->getAttributes()]);

                    $phase = 'order';
                    $order = Order::create([
                        'user_id' => $user->id, 'order_date' => now(),
                        'order_status_id' => $data['order_status_id'] ?? Order::PENDING,
                        'price' => $data['price'], 'shipping_id' => $shipping->id,
                    ]);
                    $attempt->update(['order_id' => $order->id]);
                    $completed[] = $phase;

                    foreach ($data['order_lines'] as $index => $line) {
                        $phase = 'order_line.'.$index;
                        $orderLine = $order->order_lines()->create([
                            'product_id' => $line['product_id'], 'quantity' => $line['quantity'], 'price' => $line['price'],
                        ]);
                        $completed[] = $phase;
                        $phase = 'modifiers.'.$index;
                        $orderLine->modifiers()->attach($line['modifier_ids'] ?? []);
                        $completed[] = $phase;
                    }
                    $phase = 'commit';
                    $attempt->update(['status' => 'created']);
                    // Written in the transaction: this event exists only if the order commits.
                    $audit->record('order_committed', $order, ['completed_steps' => $completed]);

                    return $order;
                });
            } catch (Throwable $exception) {
                // DB::transaction has already rolled back all business and model-audit writes.
                $attempt->refresh()->update(['status' => 'failed', 'order_id' => null]);
                $audit->record('creation_failed', properties: [
                    'phase' => $phase, 'exception' => $exception::class,
                    'message' => mb_substr($exception->getMessage(), 0, 4000),
                    'rolled_back_steps' => $completed,
                ]);
                throw $exception;
            }

            // Outside the creation catch: mail failures cannot roll back or invalidate the order.
            // If a caller owns an outer transaction, wait for its actual commit as well.
            DB::afterCommit(function () use ($order, $attempt, $audit) {
                $previous = $audit->attempt;
                $audit->attempt = $attempt;
                try {
                    $user = $order->user;
                    $this->sendOrderMail($order, new OrderToAdmin($order, $user), config('mail.admin_address'), 'admin');
                    $this->sendOrderMail($order, new OrderConfirmation($order, $user), $user->email, 'customer');
                } finally {
                    $audit->attempt = $previous;
                }
            });

            return $order;
        } finally {
            $audit->attempt = $previousAttempt;
        }
    }

    private function sendOrderMail(Order $order, Mailable $mail, ?string $recipient, string $kind): void
    {
        $audit = app(OrderAudit::class);
        try {
            $audit->record('mail_sending', $order, ['recipient_type' => $kind], system: true);
            try {
                if ($recipient === null || trim($recipient) === '') {
                    throw new \RuntimeException('Order notification recipient is not configured.');
                }
                Mail::to($recipient)->send($mail);
            } catch (Throwable $exception) {
                $audit->attempt?->update(['status' => 'created_with_mail_errors']);
                $audit->failure('mail_failed', $exception, $kind.'_mail', $order);
                Log::error('Order notification failed', [
                    'order_id' => $order->id, 'attempt_id' => $audit->attempt?->id,
                    'phase' => $kind.'_mail', 'exception' => $exception,
                ]);

                return;
            }
            $audit->record('mail_sent', $order, ['recipient_type' => $kind], system: true);
        } catch (Throwable $exception) {
            // Even an audit failure after commit must not turn a saved order into an HTTP 500.
            report($exception);
        }
    }

    /**
     * @return User
     */
    public function upsertUser(string $email, string $firstName, string $lastName, string $country, string $city, string $address, string $phoneNumber, ?string $apartment): Model
    {
        return User::query()->updateOrCreate([
            'email' => $email,
        ],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'country' => $country,
                'city' => $city,
                'address' => $address,
                'phone_number' => $phoneNumber,
                'apartment' => (string) $apartment,
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
            'apartment' => (string) $apartment,
        ]);
    }

    public function updateOrder($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $order = Order::lockForUpdate()->findOrFail($id);
            $order->update($data);

            return $order;
        });
    }

    public function deleteOrder($id)
    {
        DB::transaction(function () use ($id) {
            Order::lockForUpdate()->findOrFail($id)->delete();
        });
    }
}
