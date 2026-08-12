<?php

namespace Modules\Profiles\Providers;

use Modules\Support\ModuleServiceProvider;

class ProfilesServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Profiles';
    }
}
