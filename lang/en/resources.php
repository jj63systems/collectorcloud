<?php

return [
    'cc_locations' => [
        'fields' => [
            'id' => 'Location ID',
            'name' => 'Location Name',
            'path' => 'Full Path',
            'type' => 'Type',
            'code' => 'Code',
            'depth' => 'Depth',
            'parent' => 'Parent Location',
            'children_count' => 'Child Locations',
        ],
        'sections' => [
            'location_details' => 'Location Details',
            'location_details_description' => 'Enter the name and type of this location.',
            'hierarchy' => 'Hierarchy',
            'hierarchy_description' => 'Set the parent location to build the hierarchy.',
            'system_fields' => 'System Fields',
            'system_fields_description' => 'These values are automatically calculated.',
        ],
        'actions' => [
            'view' => 'View',
            'edit' => 'Edit',
            'delete' => 'Delete',
        ],
        'notifications' => [
            'cannot_delete' => 'This location has child locations and cannot be deleted.',
        ],
    ],
];
