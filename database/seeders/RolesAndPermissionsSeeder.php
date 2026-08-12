<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'companies.moderate',
            'jobs.moderate',
            'jobs.feature',
            'reports.view',
            'reports.export',
            'activity.view',
            'security.view',
            'permissions.manage',
            'applications.review',
            'interviews.manage',
            'messages.use',
            'talent.view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        $admin = Role::findOrCreate('admin');
        $recruiter = Role::findOrCreate('recruiter');
        $student = Role::findOrCreate('student');

        $admin->syncPermissions(Permission::all());
        $recruiter->syncPermissions([
            'applications.review',
            'interviews.manage',
            'messages.use',
            'talent.view',
        ]);
        $student->syncPermissions([
            'messages.use',
        ]);

        User::query()->each(function (User $user) {
            $role = match ($user->role) {
                UserRole::Admin => 'admin',
                UserRole::Recruiter => 'recruiter',
                default => 'student',
            };
            $user->syncRoles([$role]);
        });
    }
}
