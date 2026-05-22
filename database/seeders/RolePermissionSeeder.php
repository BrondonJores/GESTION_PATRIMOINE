<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    

public function run()
{
    $permissions = [

        // ARTICLES
        'view articles',
        'view_any articles',
        'create articles',
        'update articles',
        'delete articles',

        // CONSOMMABLES
        'view consommables',
        'create consommables',
        'update consommables',
        'delete consommables',

        // FAMILLES
        'view familles',
        'create familles',
        'update familles',
        'delete familles',

        // CATEGORIES
        'view categories',
        'create categories',
        'update categories',
        'delete categories',

        // BLOCS
        'view blocs',
        'create blocs',
        'update blocs',
        'delete blocs',

        // SALLES
        'view salles',
        'create salles',
        'update salles',
        'delete salles',

        // AFFECTATIONS
        'view affectations',
        'create affectations',
        'update affectations',
        'delete affectations',
        'reaffecter articles',
        'recuperer articles',

        // ALERTES
        'view alertes',
        'traiter alertes',
        'delete alertes',

        // NOTIFICATIONS
        'view notifications',
        'delete notifications',

        // RAPPORTS
        'view rapports',
        'create rapports',
        'export rapports',

        // LOGS
        'view logs',
        'delete logs',
        'export logs',

        // USERS
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

    // ROLES
    $admin = Role::firstOrCreate(['name' => 'admin']);
    $gestionnaire = Role::firstOrCreate(['name' => 'gestionnaire']);
    $user = Role::firstOrCreate(['name' => 'utilisateur']);

    // ADMIN → tout
    $admin->syncPermissions(Permission::all());

    // GESTIONNAIRE
    $gestionnaire->syncPermissions([
        'view articles', 'create articles', 'update articles',
        'view consommables', 'create consommables', 'update consommables',
        'view familles', 'create familles', 'update familles',
        'view categories', 'create categories', 'update categories',
        'view affectations', 'create affectations', 'update affectations',
        'reaffecter articles', 'recuperer articles',
        'view rapports', 'create rapports', 'export rapports',
        'view alertes', 'traiter alertes',
        'view notifications',
        'view blocs', 'create blocs', 'update blocs',
        'view salles', 'create salles', 'update salles'
    ]);

    // UTILISATEUR (lecture seule)
    $user->syncPermissions([
        'view articles',
        'view consommables',
        'view affectations',
        'view rapports'
    ]);
}
    }
