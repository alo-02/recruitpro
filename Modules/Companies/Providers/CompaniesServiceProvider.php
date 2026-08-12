<?php

namespace Modules\Companies\Providers;

use Modules\Support\ModuleServiceProvider;

class CompaniesServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Companies';
    }
}
