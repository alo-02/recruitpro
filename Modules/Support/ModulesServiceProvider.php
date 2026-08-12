<?php

namespace Modules\Support;

use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    /** @var list<class-string<ModuleServiceProvider>> */
    protected array $modules = [
        \Modules\Auth\Providers\AuthServiceProvider::class,
        \Modules\Profiles\Providers\ProfilesServiceProvider::class,
        \Modules\Companies\Providers\CompaniesServiceProvider::class,
        \Modules\Jobs\Providers\JobsServiceProvider::class,
        \Modules\Applications\Providers\ApplicationsServiceProvider::class,
        \Modules\Messaging\Providers\MessagingServiceProvider::class,
        \Modules\Interviews\Providers\InterviewsServiceProvider::class,
        \Modules\Admin\Providers\AdminServiceProvider::class,
        \Modules\Core\Providers\CoreServiceProvider::class,
        \Modules\Api\Providers\ApiServiceProvider::class,
        \Modules\Security\Providers\SecurityServiceProvider::class,
        \Modules\Privacy\Providers\PrivacyServiceProvider::class,
        \Modules\Growth\Providers\GrowthServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->modules as $module) {
            $this->app->register($module);
        }
    }
}
