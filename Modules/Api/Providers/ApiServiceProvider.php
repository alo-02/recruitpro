<?php

namespace Modules\Api\Providers;

use Modules\Support\ModuleServiceProvider;

class ApiServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Api';
    }

    protected function loadRoutes(): void
    {
        $api = $this->modulePath('Routes/api.php');

        if (file_exists($api)) {
            \Illuminate\Support\Facades\Route::middleware('api')
                ->prefix('api')
                ->group($api);
        }
    }
}
