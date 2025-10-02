<?php

return [

    'resources' => [
        'cc_locations' => ['view', 'create', 'update', 'delete'],
        'cc_teams' => ['view', 'create', 'update', 'delete'],
        'cc_users' => ['view', 'create', 'update', 'delete'],
        'cc_roles' => ['view', 'create', 'update', 'delete'],
    ],

    'roles' => [
        'readonly' => [
            'cc_locations.view',
            'cc_teams.view',
            'cc_users.view',
        ],

        'archivist' => [
            'cc_locations.view',
            'cc_locations.create',
            'cc_locations.update',
            'cc_teams.view',
        ],

        'archive_manager' => [
            'cc_locations.view',
            'cc_locations.create',
            'cc_locations.update',
            'cc_locations.delete',
            'cc_teams.view',
            'cc_teams.create',
            'cc_teams.update',
            'cc_teams.delete',
            'cc_users.view',
            'cc_users.update',
        ],

        'superuser' => [
            'cc_locations.view',
            'cc_locations.create',
            'cc_locations.update',
            'cc_locations.delete',
            'cc_teams.view',
            'cc_teams.create',
            'cc_teams.update',
            'cc_teams.delete',
            'cc_users.view',
            'cc_users.create',
            'cc_users.update',
            'cc_users.delete',
            'cc_roles.view',
            'cc_roles.create',
            'cc_roles.update',
            'cc_roles.delete',
        ],
    ],

];
