<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Opcodes\LogViewer\Facades\LogViewer;
use Opcodes\LogViewer\Http\Middleware\AuthorizeLogViewer;
use Tests\TestCase;

class LogViewerTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_viewer_routes_keep_application_and_package_authorization(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->getName() ?? '', 'log-viewer.')
                && ! str_contains($route->uri(), 'assets'));

        $this->assertNotEmpty($routes);
        foreach ($routes as $route) {
            $this->assertStringStartsWith('admin/log-viewer', $route->uri());
            foreach (['web', 'auth', 'admin', AuthorizeLogViewer::class] as $middleware) {
                $this->assertContains($middleware, $route->gatherMiddleware(), $route->uri());
            }
        }
    }

    public function test_guests_cannot_access_ui_or_api(): void
    {
        $this->get('/admin/log-viewer')->assertRedirect('/login');
        foreach (['hosts', 'folders', 'files', 'logs', 'files/example/download'] as $path) {
            $this->getJson('/admin/log-viewer/api/'.$path)->assertUnauthorized();
        }
        $this->deleteJson('/admin/log-viewer/api/files/example')->assertUnauthorized();
        $this->postJson('/admin/log-viewer/api/delete-multiple-files')->assertUnauthorized();
    }

    public function test_non_admins_cannot_access_ui_or_api(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => 0]));
        $this->get('/admin/log-viewer')->assertRedirect('/login');
        foreach (['hosts', 'folders', 'files', 'logs', 'files/example/download'] as $path) {
            $this->getJson('/admin/log-viewer/api/'.$path)->assertRedirect('/login');
        }
        $this->deleteJson('/admin/log-viewer/api/files/example')->assertRedirect('/login');
        $this->postJson('/admin/log-viewer/api/delete-multiple-files')->assertRedirect('/login');
        $this->assertFalse(Gate::allows('viewLogViewer'));
    }

    public function test_admin_can_open_viewer_in_production_and_package_gate_is_enforced(): void
    {
        $this->app['env'] = 'production';
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
        $this->get('/admin/log-viewer')->assertOk();
        $this->getJson('/admin/log-viewer/api/hosts')->assertOk();

        Gate::define('viewLogViewer', fn () => false);
        $this->get('/admin/log-viewer')->assertForbidden();
        $this->getJson('/admin/log-viewer/api/hosts')->assertForbidden();
    }

    public function test_admin_can_read_daily_logs_but_cannot_delete_files_folders_or_bulk_selection(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => 1]));
        $path = storage_path('logs/laravel-1900-01-01-'.uniqid('viewer-test-').'.log');
        $message = 'Log viewer daily file access test';
        file_put_contents($path, "[2026-10-23 12:00:00] testing.INFO: {$message} \n");

        try {
            $file = LogViewer::getFiles()->first(fn ($file) => $file->path === $path);
            $this->assertNotNull($file);
            $this->getJson('/admin/log-viewer/api/files')->assertOk()
                ->assertJsonFragment(['name' => basename($path), 'can_delete' => false]);
            $this->getJson('/admin/log-viewer/api/logs?file='.$file->identifier)
                ->assertOk()->assertSee($message);
            $this->deleteJson('/admin/log-viewer/api/files/'.$file->identifier)->assertForbidden();
            $this->deleteJson('/admin/log-viewer/api/folders/'.$file->subFolderIdentifier())->assertForbidden();
            // The package skips unauthorized files in a bulk request and returns success.
            $this->postJson('/admin/log-viewer/api/delete-multiple-files', ['files' => [$file->identifier]])
                ->assertOk();
            $this->assertFileExists($path);
            $this->assertStringContainsString($message, file_get_contents($path));
        } finally {
            unlink($path);
        }
    }
}
