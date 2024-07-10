<?php

namespace App\Providers;

use App\Models\Billing;
use App\Services\BillingCsvService;
use App\Services\InvoiceProcessingService;
use App\Services\LineProcessingService;
use App\Services\QueueService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use App\Services\FileProcessingService;
use Ramsey\Uuid\Uuid;
use ZipArchive;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileProcessingService::class, function ($app) {
            $zip = new ZipArchive();
            return new FileProcessingService($app->make(QueueService::class), $zip);
        });

        $this->app->singleton(LineProcessingService::class, function ($app) {
            $queueService = $app->make(QueueService::class);
            $billingModel = $app->make(Billing::class);
            return new LineProcessingService($queueService, $billingModel);
        });

        $this->app->singleton(InvoiceProcessingService::class, function ($app) {
            $billingModel = $app->make(Billing::class);
            return new InvoiceProcessingService($app->make(QueueService::class), $billingModel);
        });

        $this->app->singleton(BillingCsvService::class, function ($app) {
            $uuid4 = Uuid::uuid4();
            $zip = new ZipArchive();
            return new BillingCsvService($app->make(QueueService::class), $uuid4, $zip);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });
    }
}
