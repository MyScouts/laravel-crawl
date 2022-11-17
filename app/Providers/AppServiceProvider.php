<?php

namespace App\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        PendingRequest::macro('adequateshop', function () {
            return PendingRequest::hasMacro('');
        });
    }
}
