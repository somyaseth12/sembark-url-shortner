<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ShortUrl;
use App\Policies\ShortUrlPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(ShortUrl::class, ShortUrlPolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
