<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    

public function run()
{
    $permissions = [

        // Articles
        'view articles',
        'view_any articles',
        'create articles',
        'update articles',
        'delete articles',

        // Blocs
        'view blocs',
        'create blocs',
        'update blocs',
        'delete blocs',

        // Salles
        'view salles',
        'create salles',
        'update salles',
        'delete salles',

        // Affectations
        'view affectations',
        'create affectations',
        'update affectations',
        'delete affectations',
        'reaffecter articles',
        'recuperer articles',

        // Alertes
        'view alertes',
        'traiter alertes',
        'delete alertes',

        // Notifications
        'view notifications',
        'delete notifications',

        // Rapports
        'view rapports',
        'create rapports',
        'delete rapports',
        'export rapports',

        // Journaux
        'view logs',
        'delete logs',
        'export logs',

        // Utilisateurs
        'view users',
        'create users',
        'update users',
        'delete users',
        'assign roles',
        'reset password users',
        'activate users',
        'deactivate users',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Rôles
    $admin = Role::firstOrCreate(['name' => 'admin']);
    $gestionnaire = Role::firstOrCreate(['name' => 'gestionnaire']);
    $user = Role::firstOrCreate(['name' => 'utilisateur']);

    // Administrateur : accès complet
    $admin->syncPermissions(Permission::all());

    // Gestionnaire
    $gestionnaire->syncPermissions([
        'view articles', 'create articles', 'update articles',
        'view affectations', 'create affectations', 'update affectations',
        'reaffecter articles', 'recuperer articles',
        'view rapports', 'create rapports', 'export rapports',
        'view alertes', 'traiter alertes',
        'view notifications',
        'view blocs', 'view salles'
    ]);

    // Utilisateur : lecture seule
    $user->syncPermissions([
        'view articles',
        'view affectations',
        'view rapports'
    ]);
}
    }
