<?php

namespace App\Filament\App\Resources\CcFieldMappings;

use App\Filament\App\Resources\CcFieldMappings\Pages\CreateCcFieldMapping;
use App\Filament\App\Resources\CcFieldMappings\Pages\EditCcFieldMapping;
use App\Filament\App\Resources\CcFieldMappings\Pages\ListCcFieldMappings;
use App\Filament\App\Resources\CcFieldMappings\Schemas\CcFieldMappingForm;
use App\Filament\App\Resources\CcFieldMappings\Tables\CcFieldMappingsTable;
use App\Models\Tenant\CcFieldMapping;
use BackedEnum;
use Database\Seeders\Tenant\CcFieldMappingsSeeder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CcFieldMappingResource extends Resource
{
    protected static ?string $model = CcFieldMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'field_name';

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
