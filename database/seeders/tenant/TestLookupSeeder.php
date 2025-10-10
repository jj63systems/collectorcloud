<?php

namespace Database\Seeders\tenant;

use App\Models\Tenant\CcLookupType;
use App\Models\Tenant\CcLookupValue;
use Illuminate\Database\Seeder;

class TestLookupSeeder extends Seeder
{
    public function run(): void
    {
        $this->createLookup('INSTRUMENT_TYPE', 'Instrument Type', [
            'Oil pressure gauge',
            'Altimeter',
            'ASI',
        ]);

        $this->createLookup('MEDIA_TYPE', 'Media Type', [
            'DVD',
            'VHS',
            'BETAMAX',
            'MP4',
        ]);

        $this->createLookup('ENGINE_TYPE', 'Engine Type', [
            'Gipsy Major',
            'Merlin',
            'Centaurus',
            'Lycoming O-320',
            'Pratt & Whitney R-985',
            'Continental O-200',
            'Walter Minor',
            'Rolls-Royce Nene',
            'Bristol Hercules',
            'Junkers Jumo',
        ]);

        $this->createLookup('AIRFRAME_TYPE', 'Airframe Type', [
            'Spitfire',
            'Hurricane',
            'Tiger Moth',
            'Mosquito',
            'Lancaster',
            'Wellington',
            'Chipmunk',
            'Harvard',
            'Halifax',
            'Blenheim',
        ]);

        $this->createLookup('DOCUMENT_TYPE', 'Document Type', [
            'Flight Manual',
            'Logbook',
            'Blueprint',
            'Maintenance Record',
            'Technical Drawing',
            'Pilot Notes',
            'Airworthiness Certificate',
            'Test Report',
            'Parts Catalogue',
            'Modification Sheet',
        ]);
    }

    protected function createLookup(string $code, string $name, array $values): void
    {
        $type = CcLookupType::updateOrCreate(
            ['code' => $code],
            ['name' => $name]
        );

        foreach ($values as $index => $label) {
            CcLookupValue::updateOrCreate(
                [
                    'type_id' => $type->id,
                    'code' => strtoupper(str_replace(' ', '_', $label)),
                ],
                [
                    'label' => $label,
                    'sort_order' => $index,
                    'enabled' => true,
                    'system_flag' => false,
                ]
            );
        }
    }
}
