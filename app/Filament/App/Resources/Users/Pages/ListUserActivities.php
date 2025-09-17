<?php

namespace App\Filament\App\Resources\Users\Pages;


use App\Filament\App\Resources\Users\UserResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ListUserActivities extends ListActivities
{
    protected static string $resource = UserResource::class;
}
