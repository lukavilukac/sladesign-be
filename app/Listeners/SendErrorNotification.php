<?php

namespace App\Listeners;

use App\Mail\ApplicationError;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendErrorNotification
{
    private bool $sending = false;

    public function handle(MessageLogged $event): void
    {
        if ($this->sending || ! config('error-notifications.enabled')
            || ! in_array($event->level, ['error', 'critical', 'alert', 'emergency'], true)) {
            return;
        }

        $this->sending = true;

        try {
            $exception = $event->context['exception'] ?? null;
            $title = $exception instanceof Throwable ? $exception::class : (string) $event->message;
            $title = mb_substr(preg_replace('/[\r\n]+/', ' ', $title), 0, 180);
            // Search for the exception class or a literal fragment of the logged message.
            $query = preg_quote($title, '/');
            $url = rtrim(config('app.url'), '/').'/'.trim(config('log-viewer.route_path'), '/')
                .'?'.http_build_query(['query' => $query]);

            Mail::to(config('error-notifications.recipient'))->send(new ApplicationError(
                $title, $event->level, now()->toIso8601String(), $url,
            ));
        } catch (Throwable $exception) {
            // Do not report through Laravel's logger: that would trigger this listener again.
            error_log('Application error notification could not be sent ('.$exception::class.').');
        } finally {
            $this->sending = false;
        }
    }
}
