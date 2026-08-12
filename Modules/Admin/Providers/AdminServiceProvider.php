<?php

namespace Modules\Admin\Providers;

use Modules\Support\ModuleServiceProvider;

class AdminServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Admin';
    }
}
