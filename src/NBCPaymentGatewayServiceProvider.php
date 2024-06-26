<?php

namespace Lockminds\NBCPaymentGateway;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Lockminds\NBCPaymentGateway\Commands\NBCPaymentGatewayCommand;

class NBCPaymentGatewayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('nbc-payment-gateway')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(NBCPaymentGatewayCommand::class);
    }

    public function boot(): void
    {
        // ... other things
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('nbc-payment-gateway.route_prefix'),
//            'middleware' => config('blogpackage.middleware'),
        ];
    }

}
