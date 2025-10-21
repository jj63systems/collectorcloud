<?php

namespace App\Services;

class FieldStructureHelper
{
    public static function getStructuredFieldsByEntity(): array
    {
        return [

            'DONORS' => [
                'cc_donors.name' => ['label' => 'Name', 'type' => 'string'],
                'cc_donors.email' => ['label' => 'Email', 'type' => 'string'],
                'cc_donors.telephone' => ['label' => 'Telephone', 'type' => 'string'],
                'cc_donors.address_line_1' => ['label' => 'Address Line 1', 'type' => 'string'],
                'cc_donors.address_line_2' => ['label' => 'Address Line 2', 'type' => 'string'],
                'cc_donors.city' => ['label' => 'City', 'type' => 'string'],
                'cc_donors.county' => ['label' => 'County', 'type' => 'string'],
                'cc_donors.postcode' => ['label' => 'Postcode', 'type' => 'string'],
                'cc_donors.country' => ['label' => 'Country', 'type' => 'string'],
                'cc_donors.address_old' => ['label' => 'Legacy Address', 'type' => 'text'],
            ],

            'DONATIONS' => [
                'cc_donations.donation_name' => ['label' => 'Donation Name', 'type' => 'string'],
                'cc_donations.date_received' => ['label' => 'Date Received', 'type' => 'date'],
                'cc_donations.donation_basis' => ['label' => 'Donation Basis', 'type' => 'string'],
                'cc_donations.comments' => ['label' => 'Comments', 'type' => 'text'],
                'cc_donations.accessioned_by' => ['label' => 'Accessioned By (User ID)', 'type' => 'foreign'],
            ],

            'ITEMS' => [
                'cc_items.name' => ['label' => 'Item Name', 'type' => 'string'],
                'cc_items.description' => ['label' => 'Description', 'type' => 'text'],
                'cc_items.date_received' => ['label' => 'Date Received', 'type' => 'date'],
            ],

            'LOCATIONS' => [
                'cc_locations.name' => ['label' => 'Location Name', 'type' => 'string'],
            ],
        ];
    }
}
