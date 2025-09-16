<?php

namespace App\Filament\App\Resources\CcLocations;

use App\Filament\App\Resources\CcLocations\Pages\CreateCcLocation;
use App\Filament\App\Resources\CcLocations\Pages\EditCcLocation;
use App\Filament\App\Resources\CcLocations\Pages\ListCcLocations;
use App\Filament\App\Resources\CcLocations\Pages\ViewCcLocation;
use App\Filament\App\Resources\CcLocations\Schemas\CcLocationForm;
use App\Filament\App\Resources\CcLocations\Tables\CcLocationsTable;
use App\Models\Tenant\CcLocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CcLocationResource extends Resource
{
    protected static ?string $model = CcLocation::class;


    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings


    // ✅ Appears in sidebar navigation
    protected static ?string $navigationLabel = 'Locations';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Reference Data';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Location';         // Singular
    protected static ?string $pluralLabel = 'Locations';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 30;

    // END TITLES AND NAV SETTINGS ----------------------------

    public static function form(Schema $schema): Schema
    {
        return CcLocationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcLocationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCcLocations::route('/'),
            'create' => CreateCcLocation::route('/create'),
            'activities' => Pages\ListLocationActivities::route('/{record}/activities'),
            'view' => ViewCcLocation::route('/{record}'),
            'edit' => EditCcLocation::route('/{record}/edit'),


//            'edit' => EditCcLocation::route('/{record}/edit'),
        ];
    }
}
