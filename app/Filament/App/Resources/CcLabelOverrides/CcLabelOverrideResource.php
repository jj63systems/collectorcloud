<?php

namespace App\Filament\App\Resources\CcLabelOverrides;

use App\Filament\App\Resources\CcLabelOverrides\Pages\CreateCcLabelOverride;
use App\Filament\App\Resources\CcLabelOverrides\Pages\EditCcLabelOverride;
use App\Filament\App\Resources\CcLabelOverrides\Pages\ListCcLabelOverrides;
use App\Filament\App\Resources\CcLabelOverrides\Schemas\CcLabelOverrideForm;
use App\Filament\App\Resources\CcLabelOverrides\Tables\CcLabelOverridesTable;
use App\Models\Tenant\CcLabelOverride;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CcLabelOverrideResource extends Resource
{


    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
//    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings


    // ✅ Appears in sidebar navigation
    protected static ?string $navigationLabel = 'Screen texts';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Text';         // Singular
    protected static ?string $pluralLabel = 'Texts';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 30;

    // END TITLES AND NAV SETTINGS ----------------------------

    protected static ?string $model = CcLabelOverride::class;


    public static function form(Schema $schema): Schema
    {
        return CcLabelOverrideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcLabelOverridesTable::configure($table);
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
            'index' => ListCcLabelOverrides::route('/'),
            'create' => CreateCcLabelOverride::route('/create'),
            'edit' => EditCcLabelOverride::route('/{record}/edit'),
        ];
    }
}
