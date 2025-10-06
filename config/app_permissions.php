<?php

return [

    'resources' => [
        'cc_locations' => ['view', 'create', 'update', 'delete'],
        'cc_teams' => ['view', 'create', 'update', 'delete'],
        'users' => ['view', 'create', 'update', 'delete'],
        'roles' => ['view', 'create', 'update', 'delete'],
    ],

    'roles' => [
        'readonly' => [
            'cc_locations.view',
            'cc_teams.view',
        ],

        'archivist' => [
            'cc_locations.view',
            'cc_locations.create',
            'cc_locations.update',
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
            'users.view',
            'users.update',
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
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ],
    ],

];
