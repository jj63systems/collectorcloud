<?php

namespace App\Filament\App\Resources\CcLookupValues;

use App\Filament\App\Resources\CcLookupValues\Pages\CreateCcLookupValue;
use App\Filament\App\Resources\CcLookupValues\Pages\ListCcLookupValues;
use App\Filament\App\Resources\CcLookupValues\Schemas\CcLookupValueForm;
use App\Filament\App\Resources\CcLookupValues\Tables\CcLookupValuesTable;
use App\Models\tenant\CcLookupValue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CcLookupValueResource extends Resource
{
    protected static ?string $model = CcLookupValue::class;


    // -- TITLES AND NAV SETTINGS -----------------------------
    // Global search settings
    protected static ?string $recordTitleAttribute = 'label';
    protected static bool $isGloballySearchable = false;
    // END global search settings

    // ✅ Appears in sidebar navigation
    protected static ?string $navigationLabel = 'Lookup Values';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Reference Data';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Lookup Value';         // Singular
    protected static ?string $pluralLabel = 'Lookup Values';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 30;

    // END TITLES AND NAV SETTINGS ----------------------------


    public static function form(Schema $schema): Schema
    {
        return CcLookupValueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcLookupValuesTable::configure($table);
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
            'index' => ListCcLookupValues::route('/'),
            'create' => CreateCcLookupValue::route('/create'),
//            'edit' => EditCcLookupValue::route('/{record}/edit'),
        ];
    }
}
