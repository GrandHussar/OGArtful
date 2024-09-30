<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        Permission::create(['name' => 'edit user']);
        Permission::create(['name' => 'moderate posts']);
        Permission::create(['name' => 'edit website']);

        // Create roles and assign existing permissions
        $superAdminRole = Role::create(['name' => 'super_admin']);
        $superAdminRole->givePermissionTo(['edit user', 'moderate posts', 'edit website']);

        $itAdminRole = Role::create(['name' => 'it_admin']);
        $itAdminRole->givePermissionTo(['edit user', 'moderate posts']);

        // Create roles for user types without assigning permissions
        Role::create(['name' => 'user']);
        Role::create(['name' => 'therapist']);
    }
}
