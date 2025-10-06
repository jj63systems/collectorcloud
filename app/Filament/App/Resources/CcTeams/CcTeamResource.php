<?php

namespace App\Filament\App\Resources\CcTeams;

use App\Filament\App\Resources\CcTeams\Pages\CreateCcTeam;
use App\Filament\App\Resources\CcTeams\Pages\EditCcTeam;
use App\Filament\App\Resources\CcTeams\Pages\ListCcTeams;
use App\Filament\App\Resources\CcTeams\RelationManagers\UsersRelationManager;
use App\Filament\App\Resources\CcTeams\Schemas\CcTeamForm;
use App\Filament\App\Resources\CcTeams\Tables\CcTeamsTable;
use App\Filament\Traits\HasNavigationPermission;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Tenant\CcTeam;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CcTeamResource extends Resource
{

    use HasNavigationPermission;

    use HasResourcePermissions;

    protected static ?string $model = CcTeam::class;

    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
//    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings

    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'cc_teams.view';
    }

    protected static ?string $navigationLabel = 'Security > Teams';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Team';         // Singular
    protected static ?string $pluralLabel = 'Teams';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 95;

    // END TITLES AND NAV SETTINGS ----------------------------


    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CcTeamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcTeamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            \App\Filament\App\Resources\CcTeams\RelationManagers\RolesRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCcTeams::route('/'),
            'create' => CreateCcTeam::route('/create'),
            'edit' => EditCcTeam::route('/{record}/edit'),
        ];
    }


}
