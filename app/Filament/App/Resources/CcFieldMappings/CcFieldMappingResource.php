<?php

namespace App\Filament\App\Resources\CcFieldMappings;

use App\Filament\App\Resources\CcFieldMappings\Pages\CreateCcFieldMapping;
use App\Filament\App\Resources\CcFieldMappings\Pages\EditCcFieldMapping;
use App\Filament\App\Resources\CcFieldMappings\Pages\ListCcFieldMappings;
use App\Filament\App\Resources\CcFieldMappings\Schemas\CcFieldMappingForm;
use App\Filament\App\Resources\CcFieldMappings\Tables\CcFieldMappingsTable;
use App\Models\Tenant\CcFieldMapping;
use Database\Seeders\Tenant\CcFieldMappingsSeeder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CcFieldMappingResource extends Resource
{
    protected static ?string $model = CcFieldMapping::class;


    // -- TITLES AND NAV SETTINGS -----------------------------
    // Global search settings
    protected static ?string $recordTitleAttribute = null;
    protected static bool $isGloballySearchable = false;
    // END global search settings


    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'cc_field_mappings.view';
    }

    protected static ?string $navigationLabel = 'Setup > Catalogue Fields';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Field mapping';         // Singular
    protected static ?string $pluralLabel = 'Field mappings';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 100;

    // END TITLES AND NAV SETTINGS ----------------------------

    public static function form(Schema $schema): Schema
    {
        return CcFieldMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcFieldMappingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        $teamId = Auth::user()?->current_team_id;

        if ($teamId) {
            // Auto-seed default mappings if none exist
            if (!\App\Models\Tenant\CcFieldMapping::where('team_id', $teamId)->exists()) {
                CcFieldMappingsSeeder::seedForTeam($teamId);
            }
        }

        return parent::getEloquentQuery()
            ->with('type')
            ->when($teamId, fn($query) => $query->where('team_id', $teamId));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCcFieldMappings::route('/'),
            'create' => CreateCcFieldMapping::route('/create'),
            'edit' => EditCcFieldMapping::route('/{record}/edit'),
        ];
    }
}
