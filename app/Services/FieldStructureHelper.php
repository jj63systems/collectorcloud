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
                // Core identifiers
                'cc_items.item_key' => ['label' => 'Item Key', 'type' => 'string'],
                'cc_items.name' => ['label' => 'Item Name', 'type' => 'string'],

                // Relationships
                'cc_items.donation_id' => ['label' => 'Donated by', 'type' => 'string'],
                'cc_items.location_id' => ['label' => 'Location', 'type' => 'string'],

                // Dates and users
                'cc_items.date_received' => ['label' => 'Date Received', 'type' => 'date'],
                'cc_items.accessioned_at' => ['label' => 'Accessioned At', 'type' => 'date'],
                'cc_items.accessioned_by' => ['label' => 'Accessioned By', 'type' => 'string'],
                'cc_items.checked_by_user_id' => ['label' => 'Checked By', 'type' => 'string'],

                // Descriptions & notes
                'cc_items.description' => ['label' => 'Description', 'type' => 'text'],
                'cc_items.filing_reference' => ['label' => 'Filing Reference', 'type' => 'string'],
                'cc_items.condition_notes' => ['label' => 'Condition Notes', 'type' => 'text'],
                'cc_items.curation_notes' => ['label' => 'Curation Notes', 'type' => 'text'],

                // Lifecycle flags
                'cc_items.disposed' => ['label' => 'Disposed', 'type' => 'boolean'],
                'cc_items.disposed_date' => ['label' => 'Disposed Date', 'type' => 'date'],
                'cc_items.disposed_notes' => ['label' => 'Disposed Notes', 'type' => 'text'],

                // Optional status fields
                'cc_items.inventory_status' => ['label' => 'Inventory Status', 'type' => 'string'],
                'cc_items.is_public' => ['label' => 'Is Public', 'type' => 'boolean'],
            ], // ✅ Missing closing bracket + comma fixed here

            'LOCATIONS' => [
                'cc_locations.name' => ['label' => 'Location Name', 'type' => 'string'],
            ],
        ]; // ✅ Missing semicolon added here
    }
}
