<?php

namespace App\Http;

use App\Http\Middleware\Api\Collector;
use App\Http\Middleware\Api\AserCollector;
use App\Http\Middleware\Api\DeliveryManager;
use App\Http\Middleware\Api\Distributor;
use App\Http\Middleware\Courier;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:720,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'Login' => \App\Http\Middleware\LoginMiddleware::class,
        'Admin' => \App\Http\Middleware\Admin::class,
        'Collector' => \App\Http\Middleware\Collector::class,
        'Cashier' => \App\Http\Middleware\Cashier::class,
        'Delivery' => \App\Http\Middleware\Delivery::class,
        'Distributor' => \App\Http\Middleware\Distributor::class,
        'Operator' => \App\Http\Middleware\Operator::class,
        'Courier' => \App\Http\Middleware\Courier::class,
        'Manager' => \App\Http\Middleware\Manager::class,
        'ManagerUser' => \App\Http\Middleware\ManagerUser::class,

        //API
        'ApiCashier' => \App\Http\Middleware\Api\Cashier::class,
        'ApiCollector' => \App\Http\Middleware\Api\Collector::class,
        'ApiAserCollector' => \App\Http\Middleware\Api\AserCollector::class,
        'ApiDeliveryManager' => \App\Http\Middleware\Api\DeliveryManager::class,
        'ApiDistributor' => \App\Http\Middleware\Api\Distributor::class,
        'ApiOperator' => \App\Http\Middleware\Api\Operator::class,
        'BonAz' => \App\Http\Middleware\Api\BonAz::class,
        'Yigim' => \App\Http\Middleware\Api\Yigim::class,
        'ColibriIT' => \App\Http\Middleware\Api\ColibriIT::class,
        'CourierPanel' => \App\Http\Middleware\Api\CourierPanel::class,
        'Azerpost' => \App\Http\Middleware\Api\Azerpost::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
