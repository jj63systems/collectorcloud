<?php

namespace App\Filament\App\Resources\CcLocations;

use App\Filament\App\Resources\CcLocations\Pages\CreateCcLocation;
use App\Filament\App\Resources\CcLocations\Pages\EditCcLocation;
use App\Filament\App\Resources\CcLocations\Pages\ListCcLocations;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Locations';

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
//            'edit' => EditCcLocation::route('/{record}/edit'),
        ];
    }
}
