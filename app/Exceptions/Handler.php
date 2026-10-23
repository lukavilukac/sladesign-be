<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    protected function context(): array
    {
        return array_merge(parent::context(), array_filter([
            'attempt_id' => request()->attributes->get('order_attempt_id'),
            'request_id' => request()->attributes->get('order_audit_request_id'),
        ]));
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (request()->attributes->has('order_attempt_id') || request()->attributes->has('order_audit_request_id')) {
                request()->attributes->set('order_reported_exception', $e);
            }
        });
    }
}
