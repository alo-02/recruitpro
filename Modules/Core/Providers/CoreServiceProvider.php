<?php

namespace Modules\Core\Providers;

use Modules\Support\ModuleServiceProvider;

class CoreServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Core';
    }
}
