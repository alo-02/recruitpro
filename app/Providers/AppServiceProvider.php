<?php

namespace App\Providers;

use App\Services\SiteBranding;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteBranding::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $root = (string) config('app.url');
        if ($root !== '') {
            URL::forceRootUrl($root);
            $scheme = parse_url($root, PHP_URL_SCHEME);
            if (is_string($scheme) && $scheme !== '') {
                URL::forceScheme($scheme);
            }
        }

        View::composer('*', function ($view) {
            if (! isset($view->getData()['brand'])) {
                $view->with('brand', app(SiteBranding::class));
            }
        });

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            return null;
        });
    }
}
