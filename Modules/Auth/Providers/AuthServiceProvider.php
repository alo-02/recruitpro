<?php

namespace Modules\Auth\Providers;

use Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Auth';
    }
}
