<?php

namespace Modules\Applications\Providers;

use Modules\Support\ModuleServiceProvider;

class ApplicationsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Applications';
    }
}
