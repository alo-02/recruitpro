<?php

namespace Modules\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract public function moduleName(): string;

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadMigrations();
    }

    protected function modulePath(string $path = ''): string
    {
        $base = base_path('Modules/'.$this->moduleName());

        return $path === '' ? $base : $base.'/'.ltrim($path, '/');
    }

    protected function loadRoutes(): void
    {
        $web = $this->modulePath('Routes/web.php');

        if (file_exists($web)) {
            Route::middleware('web')->group($web);
        }
    }

    protected function loadViews(): void
    {
        $views = $this->modulePath('Resources/views');

        if (is_dir($views)) {
            $this->loadViewsFrom($views, strtolower($this->moduleName()));
        }
    }

    protected function loadMigrations(): void
    {
        $migrations = $this->modulePath('Database/Migrations');

        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }
}
