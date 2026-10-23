<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Application logs

Admins can open `/admin/log-viewer` (locally, `https://webshop.test/admin/log-viewer`)
from the **Application Logs** navigation link. Both the UI and API use the existing
`web`, `auth`, and `admin` middleware plus the package's `AuthorizeLogViewer`
middleware. The `viewLogViewer` gate uses `User::isAdmin()`, including in production.
File, folder, and bulk deletion are disabled through the package's deletion gates;
there is no separate privileged role in this application.

Opcodes Log Viewer 3.24.2 supports this application's Laravel 13 / PHP 8.5 runtime.
Configuration was published using `php artisan vendor:publish --tag=log-viewer-config`.
Assets were published using `php artisan log-viewer:publish`; Composer's
`post-autoload-dump` hook republishes them on install/update. Published assets are
generated and ignored by Git. Although 3.24.2 can serve assets directly, publishing
is retained per the [installation documentation](https://log-viewer.opcodes.io/docs/3.x/install).
If deploying with Composer scripts disabled, run `php artisan log-viewer:publish`
manually. Rebuild the admin frontend with `npm run build` and refresh configuration
and route caches using your normal deployment process after updating the code.

The viewer includes `storage/logs/*.log` and nested `.log` files, covering both
`laravel.log` and daily `laravel-YYYY-MM-DD.log` files. The PHP web process needs read
access to these files and write access to Laravel's configured cache store.
The existing logging channel remains unchanged (`stack` → `single` by default);
`daily` remains available with its existing 14-day retention setting.

Run focused access-control, daily-file reading, and deletion protection tests:

```sh
docker compose -f docker/docker-compose.yml run --rm --no-deps php php artisan test --filter=LogViewerTest
```

## Error email notifications

Laravel log events at `error`, `critical`, `alert`, or `emergency` send an email to
`babic.milos22@gmail.com` through the existing default mailer. Reported exceptions
use their class name as the subject; explicit error logs use the first 180 characters
of the message. The email includes the environment, timestamp, and a Log Viewer
search link. Exception messages, stack traces, request data, and log context are not
copied into exception emails. Do not put secrets into explicit log message titles.

Configure `ERROR_NOTIFICATIONS_ENABLED` (default `true`) and
`ERROR_NOTIFICATIONS_RECIPIENT` to override these settings. `APP_URL` must be the
correct public application URL so links work for HTTP requests and CLI jobs alike.
Use working production `MAIL_*` SMTP settings and refresh the configuration cache
after changing environment variables. Local Docker overrides SMTP to Mailpit;
inspect local messages at `http://localhost:8026` rather than in Gmail.

Sending is synchronous and does not require a queue worker. There is no throttling:
each qualifying log event sends an email. Mail errors are contained and written to
PHP's error output, without recursive notifications or retries. This cannot notify
about failures before Laravel boots, failures of the logger itself, or exceptions
Laravel intentionally does not report (for example validation errors and 404s).

## Public checkout customer lookup

Send `POST /api/customers/lookup` with JSON `{"email":"customer@example.com"}`
and `Accept: application/json`. Email matching ignores case and surrounding spaces.
The response is `{"exists":true,"user":{...},"shipping":{...}}`.
`user` contains only `email`, `first_name`, `last_name`, `phone_number`, `country`,
`city`, `address`, and `apartment`. `shipping` contains `country`, `city`, `address`,
`apartment`, `phone_number`, and `postal_code`. Phone numbers are returned as stored,
including their dialing prefix; postal codes are not currently stored and return null.
Shipping is the newest shipping row (created_at, then id) linked through any of this
customer's orders. A customer with no shipping returns `shipping: null`.

Unknown emails, the configured `MAIL_ADMIN_ADDRESS`, and admin accounts all return
`{"exists":false,"user":null,"shipping":null}` with HTTP 200. Invalid input returns
422; requests beyond 10 per minute per IP return 429. Responses disable caching.
This public endpoint intentionally returns customer contact/address data using only
an email lookup; it does not verify ownership of that email. It sends no mail and
does not change the customer, shipping, or order audit history.

## Order audit history

Spatie Activitylog 5.1.3 records order history in `activity_log`. Admins can open
**History and errors** on `/admin/orders/{id}` or **Order Attempts** at
`/admin/order-attempts`. Attempts include requests rejected by validation and
requests that rolled back before an order existed. Each attempt has a server UUID,
returned in the `X-Order-Attempt-Id` response header. The API still returns HTTP 201
when an order is saved, even if sending a notification fails.

Deploy with the usual dependency installation and asset build, then run:

```sh
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
```

Run migrations before accepting order traffic on the new code. There are two new
tables (`activity_log`, `order_attempts`); existing business tables are unchanged.
The creation service uses `DB::transaction`, and model audit writes participate in
that transaction. Failures are recorded after rollback with the failed phase and
completed-but-rolled-back steps. Notification delivery runs after the outermost
commit, with separate success/failure records for admin and customer mail. A mail
failure never rolls back an order or prevents attempting the other notification.
`mail_sent` means the configured mailer accepted the message, not confirmed inbox
delivery. There is no automatic retry or background queue added by this change.

The received payload is the Laravel-normalized request body before validation,
restricted to checkout business fields; nested secret fields are redacted. The
validated payload is stored separately. No auth headers, cookies, passwords, or
card details are captured. The UI is protected by the existing `auth` and `admin`
middleware. There are no audit-edit/delete routes. Payloads contain customer PII;
back up and restrict database access accordingly. No automatic pruning is scheduled;
set a retention policy before scheduling cleanup. Spatie's `activitylog:clean`
(default 365 days) removes activities, but does not prune `order_attempts` payloads.

An optional `checkout_id` UUID can be sent by the storefront to find related retries.
Each request still receives a separate attempt ID. This is correlation only, **not
idempotency**, and supplied checkout IDs are not treated as proof of identity.
Existing clients need no change. Events use the authenticated actor (with a name /
email snapshot), Guest for unauthenticated checkout, or System for background work.
The customer attached to an order is not assumed to be the authenticated actor.

Eloquent observers cover orders, order lines, modifier pivot attach/detach, shipping,
and customer contact changes. Shared customer/shipping changes appear on every
affected order. Status changes use a row lock and save their audit in the same
transaction. Use Eloquent instances and transactions for future mutations: bulk SQL,
query-builder updates/deletes, `saveQuietly`, and database cascades bypass observers.
Historical activity before this feature cannot be reconstructed. A caller wrapping
creation in its own transaction also owns persistence of the attempt itself; rolling
back that outer transaction discards it. HTTP checkout begins its attempt outside
the business transaction, so failed attempts remain visible.

Error emails and technical logs include the attempt/request correlation ID. Detailed
audit errors remain in the database after rotating log files; the technical-log link
only finds entries that still exist in the configured log files. Failures before
Laravel/middleware runs, a lost database connection, or a killed process cannot be
reliably recorded by this database-backed audit (interrupted attempts may remain
`received`/`processing`).

Focused tests exercise real commits without wrapping the test in a transaction:

```sh
docker compose -f docker/docker-compose.yml run --rm --no-deps php php artisan test --filter=OrderAuditTest
```
