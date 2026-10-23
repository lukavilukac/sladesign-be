<?php

namespace App\Http\Middleware;

use App\Models\Order\Order;
use App\Services\OrderAudit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class TrackOrderChange
{
    public function handle(Request $request, Closure $next)
    {
        $order = Order::findOrFail($request->route('orderId'));
        $request->attributes->set('order_audit_request_id', (string) Str::uuid());
        $audit = app(OrderAudit::class);
        $audit->record('change_requested', $order, ['payload' => $audit->payload($request->only('order_status_id'))]);
        try {
            $response = $next($request);
            $validationRedirect = in_array('errors', session()->get('_flash.new', []), true);
            if ($response->getStatusCode() >= 400 || $validationRedirect) {
                if ($exception = $request->attributes->get('order_reported_exception')) {
                    $audit->failure('change_failed', $exception, 'status_update', $order);
                } else {
                    $audit->record('change_failed', $order, [
                        'http_status' => $response->getStatusCode(),
                        'errors' => $response->getStatusCode() === 422 ? ($response->getData(true)['errors'] ?? [])
                            : ($validationRedirect ? session('errors')->getBag('default')->messages() : []),
                    ]);
                }
            }

            return $response;
        } catch (Throwable $exception) {
            $audit->failure('change_failed', $exception, 'status_update', $order);
            throw $exception;
        }
    }
}
