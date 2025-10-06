<?php

namespace App\Filament\App\Resources\Users;

use App\Filament\App\Resources\Users\Pages\CreateUser;
use App\Filament\App\Resources\Users\Pages\EditUser;
use App\Filament\App\Resources\Users\Pages\ListUsers;
use App\Filament\App\Resources\Users\Pages\ViewUser;
use App\Filament\App\Resources\Users\Schemas\UserForm;
use App\Filament\App\Resources\Users\Schemas\UserInfolist;
use App\Filament\App\Resources\Users\Tables\UsersTable;
use App\Filament\Traits\HasNavigationPermission;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Tenant\TenantUser as User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{

    use HasNavigationPermission;

    use HasResourcePermissions;

    protected static ?string $model = User::class;


    // -- TITLES AND NAV SETTINGS -----------------------------

    // Global search settings
//    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = false;
    // END global search settings

    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'users.view';
    }

    protected static ?string $navigationLabel = 'Security > User management';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Configure';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'User';         // Singular
    protected static ?string $pluralLabel = 'Users';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 90;

    // END TITLES AND NAV SETTINGS ----------------------------


    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
            'activities' => Pages\ListUserActivities::route('/{record}/activities'),

        ];
    }
}
