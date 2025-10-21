<?php

namespace App\Filament\App\Resources\CcItemStages;

use App\Filament\App\Resources\CcItemStages\Pages\ListCcItemStages;
use App\Filament\App\Resources\CcItemStages\Schemas\CcItemStageForm;
use App\Filament\App\Resources\CcItemStages\Tables\CcItemStagesTable;
use App\Models\Tenant\CcItemStage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CcItemStageResource extends Resource
{
    protected static ?string $model = CcItemStage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CcItemStageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcItemStagesTable::configure($table);
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
            'index' => ListCcItemStages::route('/'),
//            'create' => CreateCcItemStage::route('/create'),
//            'edit' => EditCcItemStage::route('/{record}/edit'),
        ];
    }
}
