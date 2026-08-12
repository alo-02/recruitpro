<?php

namespace Modules\Growth\Providers;

use Modules\Support\ModuleServiceProvider;

class GrowthServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Growth';
    }
}
