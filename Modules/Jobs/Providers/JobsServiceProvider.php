<?php

namespace Modules\Jobs\Providers;

use Modules\Support\ModuleServiceProvider;

class JobsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Jobs';
    }
}
