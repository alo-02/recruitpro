<?php

namespace Modules\Messaging\Providers;

use Modules\Support\ModuleServiceProvider;

class MessagingServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'Messaging';
    }
}
