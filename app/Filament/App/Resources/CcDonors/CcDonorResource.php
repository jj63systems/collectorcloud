<?php

namespace App\Filament\App\Resources\CcDonors;

use App\Filament\App\Resources\CcDonors\Pages\CreateCcDonor;
use App\Filament\App\Resources\CcDonors\Pages\EditCcDonor;
use App\Filament\App\Resources\CcDonors\Pages\ListCcDonors;
use App\Filament\App\Resources\CcDonors\Schemas\CcDonorForm;
use App\Filament\App\Resources\CcDonors\Tables\CcDonorsTable;
use App\Models\Tenant\CcDonor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CcDonorResource extends Resource
{
    protected static ?string $model = CcDonor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CcDonorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CcDonorsTable::configure($table);
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
            'index' => ListCcDonors::route('/'),
            'create' => CreateCcDonor::route('/create'),
            'edit' => EditCcDonor::route('/{record}/edit'),
        ];
    }
}
