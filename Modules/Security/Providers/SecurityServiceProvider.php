<?php

namespace Modules\Security\Providers;

use Modules\Support\ModuleServiceProvider;

class SecurityServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Security';
    }
}
