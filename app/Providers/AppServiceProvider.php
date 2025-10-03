<?php

namespace App\Providers;

use App\Http\Responses\Auth\LoginResponse;
use App\Services\LabelService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(LoginResponseContract::class, LoginResponse::class);

        $this->app->singleton(LabelService::class, function ($app) {
            return new LabelService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::forceScheme('https');
    }


}
