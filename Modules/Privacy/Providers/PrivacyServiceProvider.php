<?php

namespace Modules\Privacy\Providers;

use Modules\Support\ModuleServiceProvider;

class PrivacyServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Privacy';
    }
}
