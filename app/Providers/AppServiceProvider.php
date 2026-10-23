<?php

namespace App\Providers;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Services\Category\ApiCategoryService;
use App\Http\Controllers\Api\Services\Category\EnApiCategoryService;
use App\Http\Controllers\Api\Services\Category\IApiCategoryServiceInterface;
use App\Http\Controllers\Api\Services\Product\ApiProductService;
use App\Http\Controllers\Api\Services\Product\EnApiProductService;
use App\Http\Controllers\Api\Services\Product\IApiProductServiceInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->scoped(\App\Services\OrderAudit::class);
        $this->app->when(CategoryController::class)
            ->needs(IApiCategoryServiceInterface::class)
            ->give(function ($app) {
                // Your logic to determine which implementation to return
                if (App::getLocale() === 'en') {
                    return new EnApiCategoryService();
                }

                return new ApiCategoryService();
            });

        $this->app->when(ProductController::class)
            ->needs(IApiProductServiceInterface::class)
            ->give(function ($app) {
                if (App::getLocale() === 'en') {
                    return new EnApiProductService();
                }

                return new ApiProductService();
            });

        $this->app->when(ProductController::class)
            ->needs(IApiCategoryServiceInterface::class)
            ->give(function ($app) {
                if (App::getLocale() === 'en') {
                    return new EnApiCategoryService();
                }

                return new ApiCategoryService();
            });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(\App\Listeners\SendErrorNotification::class);
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Log\Events\MessageLogged::class,
            [\App\Listeners\SendErrorNotification::class, 'handle'],
        );

        foreach ([\App\Models\Order\Order::class, \App\Models\OrderLine\OrderLine::class,
            \App\Models\OrderLine\OrderLineModifier::class, \App\Models\Shipping\Shipping::class, \App\Models\User::class] as $model) {
            $model::observe(\App\Observers\OrderHistoryObserver::class);
        }

        //category service
        $this->app->bind(IApiCategoryServiceInterface::class, function () {
            if (App::getLocale() === 'en') {
                return new EnApiCategoryService();
            } else {
                return new ApiCategoryService();
            }
        });
    }
}
