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
    protected static ?string $model = CcLabelOverride::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
