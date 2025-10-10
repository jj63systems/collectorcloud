<?php

namespace App\Filament\App\Resources\CcItems;

use App\Filament\App\Resources\CcItems\Pages\CreateCcItem;
use App\Filament\App\Resources\CcItems\Pages\EditCcItem;
use App\Filament\App\Resources\CcItems\Pages\ListCcItems;
use App\Filament\App\Resources\CcItems\Schemas\CcItemForm;
use App\Filament\App\Resources\CcItems\Tables\CcItemsTable;
use App\Models\Tenant\CcItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CcItemResource extends Resource
{
    protected static ?string $model = CcItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

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

    public static function getPages(): array
    {
        return [
            'index' => ListCcItems::route('/'),
            'create' => CreateCcItem::route('/create'),
            'edit' => EditCcItem::route('/{record}/edit'),
        ];
    }
}
