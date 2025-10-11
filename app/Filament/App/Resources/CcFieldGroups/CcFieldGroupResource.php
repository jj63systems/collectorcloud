<?php

namespace App\Filament\App\Resources\CcFieldGroups;

use App\Filament\App\Resources\CcFieldGroups\Pages\CreateCcFieldGroup;
use App\Filament\App\Resources\CcFieldGroups\Pages\EditCcFieldGroup;
use App\Filament\App\Resources\CcFieldGroups\Pages\ListCcFieldGroups;
use App\Filament\App\Resources\CcFieldGroups\Schemas\CcFieldGroupForm;
use App\Filament\App\Resources\CcFieldGroups\Tables\CcFieldGroupsTable;
use App\Models\Tenant\CcFieldGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


class CcFieldGroupResource extends Resource
{
    protected static ?string $model = CcFieldGroup::class;

    // -- TITLES AND NAV SETTINGS -----------------------------
    // Global search settings
    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = true;
    // END global search settings


    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'cc_field_groups.view';
    }

    protected static ?string $navigationLabel = 'System > Catalogue Field Groups';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Field Group';         // Singular
    protected static ?string $pluralLabel = 'Field Groups';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 10;

    // END TITLES AND NAV SETTINGS ----------------------------


    public static function form(Schema $schema): Schema
    {
        return CcFieldGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcFieldGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('team_id', Auth::user()?->current_team_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCcFieldGroups::route('/'),
            'create' => CreateCcFieldGroup::route('/create'),
            'edit' => EditCcFieldGroup::route('/{record}/edit'),
        ];
    }
}
