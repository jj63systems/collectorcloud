<?php
// config/label_keys.php

return [

    'cc_locations' => [
        // Sections
        'sections.location_details' => 'Location Details',
        'sections.location_details_description' => 'Enter the name and type of this location.',
        'sections.hierarchy' => 'Hierarchy',
        'sections.hierarchy_description' => 'Set the parent location to build the hierarchy.',
        'sections.system_fields' => 'System Fields',
        'sections.system_fields_description' => 'These values are automatically calculated.',

        // Fields (form + table)
        'fields.id' => 'Location ID',
        'fields.name' => 'Location Name',
        'fields.type' => 'Type',
        'fields.parent' => 'Parent Location',
        'fields.path' => 'Full Path',
        'fields.depth' => 'Depth',
        'fields.children_count' => 'Child Locations',
        // 'fields.code' => 'Code', // commented in your table class, include if you plan to expose
    ],

    // other resources will go here...
];
