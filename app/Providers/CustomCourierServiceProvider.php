<?php

namespace App\Providers;

use App\Contracts\CourierServiceInterface;
use App\Services\Colibri189CourierService;
use Illuminate\Support\ServiceProvider;

class CustomCourierServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CourierServiceInterface::class, Colibri189CourierService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
