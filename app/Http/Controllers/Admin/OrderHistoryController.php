<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order\Order;
use App\Models\Order\OrderAttempt;
use App\Services\OrderAudit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class OrderHistoryController
{
    public function attempts(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:received,processing,validation_failed,failed,created,created_with_mail_errors',
            'checkout_id' => 'nullable|uuid',
        ]);
        $attempts = OrderAttempt::query()->select(['id', 'checkout_id', 'order_id', 'status', 'created_at'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['checkout_id'] ?? null, fn ($query, $id) => $query->where('checkout_id', $id))
            ->latest()->orderByDesc('id')->paginate(25)->withQueryString();

        return Inertia::render('Admin/Orders/Attempts', ['attempts' => $attempts, 'filters' => $filters]);
    }

    public function attempt(OrderAttempt $attempt)
    {
        return Inertia::render('Admin/Orders/Attempt', ['attempt' => $attempt]);
    }

    public function orderHistory(Request $request, Order $order)
    {
        $query = Activity::where('log_name', 'orders')->where(function ($query) use ($order) {
            $query->where('order_id', $order->id)->orWhereIn('attempt_id',
                OrderAttempt::where('order_id', $order->id)->select('id'));
        });

        return $this->history($request, $query);
    }

    public function attemptHistory(Request $request, OrderAttempt $attempt)
    {
        return $this->history($request, Activity::where('log_name', 'orders')->where('attempt_id', $attempt->id));
    }

    private function history(Request $request, $query)
    {
        $filters = $request->validate(['type' => 'nullable|in:all,errors,changes']);
        if (($filters['type'] ?? null) === 'errors') {
            $query->whereIn('event', ['creation_failed', 'validation_failed', 'request_failed', 'mail_failed', 'change_failed']);
        } elseif (($filters['type'] ?? null) === 'changes') {
            $query->where('event', 'like', '%.%');
        }

        return $query->orderByDesc('id')->paginate(25)->through(function ($entry) {
            $identifier = $entry->properties['request_id'] ?? $entry->attempt_id;

            return [
                'id' => $entry->id, 'event' => $entry->event, 'created_at' => $entry->created_at,
                'properties' => $entry->properties, 'changes' => $entry->attribute_changes,
                'attempt_id' => $entry->attempt_id,
                'log_url' => $identifier ? OrderAudit::viewerUrl($identifier) : null,
            ];
        });
    }
}
