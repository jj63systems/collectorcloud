<?php

namespace App\Filament\App\Resources\Roles;

use App\Filament\App\Resources\Roles\Pages\CreateRole;
use App\Filament\App\Resources\Roles\Pages\EditRole;
use App\Filament\App\Resources\Roles\Pages\ListRoles;
use App\Filament\App\Resources\Roles\Schemas\RoleForm;
use App\Filament\App\Resources\Roles\Tables\RolesTable;
use App\Filament\Traits\HasNavigationPermission;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Tenant\Role;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RoleResource extends Resource
{

    use HasNavigationPermission;

    use HasResourcePermissions;

    protected static ?string $model = Role::class;

    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
//    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings

    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'roles.view';
    }

    protected static ?string $navigationLabel = 'Security > Roles';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Role';         // Singular
    protected static ?string $pluralLabel = 'Roles';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 97;

    // END TITLES AND NAV SETTINGS ----------------------------

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\App\Resources\Roles\RelationManagers\PermissionsRelationManager::class,
            \App\Filament\App\Resources\Roles\RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
