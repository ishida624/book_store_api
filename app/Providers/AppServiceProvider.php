<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\OutPutTimeService;
use App\ImportBookStoreService;
use App\ImportUserDataService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('OutputTimeService', function () {
            return new OutPutTimeService;
        });
        $this->app->singleton('ImportBookStoreService', function () {
            return new ImportBookStoreService;
        });
        $this->app->singleton('ImportUserDataService', function () {
            return new ImportUserDataService;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
