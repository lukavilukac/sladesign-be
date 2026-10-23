<?php

namespace App\Http\Middleware;

use App\Services\OrderAudit;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class TrackOrderAttempt
{
    public function handle(Request $request, Closure $next)
    {
        $audit = app(OrderAudit::class);
        $attempt = $audit->begin($request->all());
        $request->attributes->set('order_attempt_id', $attempt->id);

        try {
            $response = $next($request);
            // Laravel's routing pipeline renders validation exceptions before returning here.
            if ($attempt->fresh()->status === 'received' && $response->getStatusCode() >= 400) {
                $validation = $response->getStatusCode() === 422;
                $attempt->update(['status' => $validation ? 'validation_failed' : 'failed']);
                if ($exception = $request->attributes->get('order_reported_exception')) {
                    $audit->failure('request_failed', $exception, 'request');
                } else {
                    $audit->record($validation ? 'validation_failed' : 'request_failed', properties: [
                        'http_status' => $response->getStatusCode(),
                        'errors' => $validation ? ($response->getData(true)['errors'] ?? []) : [],
                    ]);
                }
            }
            $response->headers->set('X-Order-Attempt-Id', $attempt->id);

            return $response;
        } catch (Throwable $exception) {
            if ($attempt->fresh()->status === 'received') {
                $attempt->update(['status' => 'failed']);
                $audit->failure('request_failed', $exception, 'request');
            }
            throw $exception;
        } finally {
            $audit->attempt = null;
        }
    }
}
