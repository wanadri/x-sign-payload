<?php

declare(strict_types=1);

namespace Wanadri\XSignPayload\Frameworks\Laravel;

use Illuminate\Support\ServiceProvider;
use Wanadri\XSignPayload\Frameworks\Laravel\Commands\InstallCommand;
use Wanadri\XSignPayload\Frameworks\Laravel\Middleware\VerifyXSignPayload;

class XSignPayloadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/x-sign-payload.php',
            'x-sign-payload'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../../../config/x-sign-payload.php' => config_path('x-sign-payload.php'),
            ], 'config');
        }

        // Register middleware alias for Laravel 10+
        $router = $this->app->make('router');
        $router->aliasMiddleware('x-sign.verify', VerifyXSignPayload::class);
    }
}
