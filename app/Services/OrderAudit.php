<?php

namespace App\Services;

use App\Models\Order\Order;
use App\Models\Order\OrderAttempt;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Throwable;

class OrderAudit
{
    public ?OrderAttempt $attempt = null;

    public function payload(array $data): array
    {
        // Only business fields are persisted, including invalid values for troubleshooting.
        $payload = Arr::only($data, ['checkout_id', 'email_address', 'first_name', 'last_name',
            'country_name', 'country_code', 'city', 'address', 'phone_number', 'apartment', 'postal_code',
            'price', 'order_lines', 'order_status_id']);
        if (isset($payload['order_lines']) && is_array($payload['order_lines'])) {
            $payload['order_lines'] = array_map(fn ($line) => is_array($line)
                ? Arr::only($line, ['product_id', 'quantity', 'price', 'modifier_ids']) : $line,
                $payload['order_lines']);
        }

        return $this->redact($payload);
    }

    private function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (preg_match('/password|secret|token|authorization|cookie|card|cvv|cvc/i', (string) $key)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->redact($value);
            }
        }

        return $data;
    }

    public function begin(array $payload, string $source = 'http'): OrderAttempt
    {
        $checkout = $payload['checkout_id'] ?? null;
        $this->attempt = OrderAttempt::create([
            'id' => (string) Str::uuid(),
            'checkout_id' => is_string($checkout) && Str::isUuid($checkout) ? $checkout : null,
            'actor_id' => Auth::id(),
            'status' => 'received',
            'payload' => $this->payload($payload),
        ]);
        $this->record('request_received', null, ['source' => $source]);

        return $this->attempt;
    }

    public function record(string $event, ?Order $order = null, array $properties = [], array $changes = [], bool $system = false): void
    {
        $user = $system ? null : Auth::user();
        $actor = $user ? ['id' => $user->id, 'name' => trim($user->first_name.' '.$user->last_name), 'email' => $user->email]
            : ['name' => ($system || (app()->runningInConsole() && ! request()->route())) ? 'System' : 'Guest'];
        $entry = activity('orders')->event($event)->withProperties(array_merge($properties, ['actor' => $actor, 'request_id' => request()->attributes->get('order_audit_request_id')]));
        $user ? $entry->causedBy($user) : $entry->causedByAnonymous();
        if ($order) {
            $entry->performedOn($order);
        }
        $entry->tap(function (Activity $activity) use ($order, $changes) {
            $activity->order_id = $order?->id ?? $this->attempt?->order_id;
            $activity->attempt_id = $this->attempt?->id;
            $activity->attribute_changes = $changes;
        })->log($event);
    }

    public function failure(string $event, Throwable $exception, string $phase, ?Order $order = null): void
    {
        $this->record($event, $order, [
            'phase' => $phase,
            'exception' => $exception::class,
            'message' => mb_substr($exception->getMessage(), 0, 4000),
        ], system: true);
    }

    public static function viewerUrl(string $identifier): string
    {
        return rtrim(config('app.url'), '/').'/'.trim(config('log-viewer.route_path'), '/')
            .'?'.http_build_query(['query' => preg_quote($identifier, '/')]);
    }
}
