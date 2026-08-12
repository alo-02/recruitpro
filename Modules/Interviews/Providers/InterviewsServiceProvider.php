<?php

namespace Modules\Interviews\Providers;

use Modules\Support\ModuleServiceProvider;

class InterviewsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Interviews';
    }
}
