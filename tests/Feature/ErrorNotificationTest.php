<?php

namespace Tests\Feature;

use App\Mail\ApplicationError;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class ErrorNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['error-notifications.enabled' => true]);
        Mail::fake();
    }

    public function test_reported_exception_sends_one_email_with_search_link(): void
    {
        report(new RuntimeException('Private exception details'));

        Mail::assertSent(ApplicationError::class, function ($mail) {
            $this->assertSame(RuntimeException::class, $mail->errorTitle);
            $this->assertStringContainsString('/admin/log-viewer?query=RuntimeException', $mail->viewerUrl);
            $this->assertStringContainsString('RuntimeException', $mail->envelope()->subject);
            $this->assertStringNotContainsString('Private exception details', $mail->render());

            return $mail->hasTo('babic.milos22@gmail.com');
        });
        Mail::assertSentCount(1);
    }

    public function test_all_error_levels_send_but_lower_levels_do_not(): void
    {
        foreach (['debug', 'info', 'notice', 'warning'] as $level) {
            Log::log($level, 'No email');
        }
        Mail::assertNothingSent();

        foreach (['error', 'critical', 'alert', 'emergency'] as $level) {
            Log::log($level, 'Application failure');
        }
        Mail::assertSentCount(4);
        Mail::assertSent(ApplicationError::class, fn ($mail) => $mail->errorTitle === 'Application failure');
    }

    public function test_notifications_can_be_disabled(): void
    {
        config(['error-notifications.enabled' => false]);
        Log::error('Disabled notification');
        Mail::assertNothingSent();
    }

    public function test_mail_failure_and_nested_logs_do_not_recurse_or_break_reporting(): void
    {
        $pending = \Mockery::mock(\Illuminate\Mail\PendingMail::class);
        Mail::shouldReceive('to')->twice()->andReturn($pending);
        $pending->shouldReceive('send')->twice()->andReturnUsing(function () {
            Log::error('Nested mail failure');
            throw new RuntimeException('SMTP unavailable');
        });

        Log::error('First application error');
        Log::error('Second application error');
        $this->assertTrue(true); // Both calls complete, and the guard resets after failure.
    }
}
