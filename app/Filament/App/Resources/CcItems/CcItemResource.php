<?php

namespace App\Filament\App\Resources\CcItems;

use App\Filament\App\Resources\CcItems\Pages\CreateCcItem;
use App\Filament\App\Resources\CcItems\Pages\EditCcItem;
use App\Filament\App\Resources\CcItems\Pages\ListCcItems;
use App\Filament\App\Resources\CcItems\Schemas\CcItemForm;
use App\Filament\App\Resources\CcItems\Tables\CcItemsTable;
use App\Models\Tenant\CcItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


class CcItemResource extends Resource
{
    protected static ?string $model = CcItem::class;

    // -- TITLES AND NAV SETTINGS -----------------------------
    // Global search settings
    protected static ?string $recordTitleAttribute = 'name';
    protected static bool $isGloballySearchable = true;
    // END global search settings


    // ✅ Appears in sidebar navigation

    protected static function getNavigationPermission(): string
    {
        return 'cc_items.view';
    }

    protected static ?string $navigationLabel = 'Catalogue';

    // ✅ Icon in navigation (any Blade Heroicon or Lucide icon name)
//    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // ✅ Optional grouping in sidebar
    protected static string|null|\UnitEnum $navigationGroup = 'Collection';

    // ✅ Title shown on the List Records page
    protected static ?string $label = 'Catalogue Item';         // Singular
    protected static ?string $pluralLabel = 'Catalogue Items';  // Plural

    // ✅ Optional custom navigation sort
    protected static ?int $navigationSort = 10;

    // END TITLES AND NAV SETTINGS ----------------------------


    public static function form(Schema $schema): Schema
    {
        return CcItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcItemsTable::configure($table);
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

        return parent::getEloquentQuery()
            ->where('team_id', $teamId);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCcItems::route('/'),
            'create' => CreateCcItem::route('/create'),
            'edit' => EditCcItem::route('/{record}/edit'),
        ];
    }
}
