<?php

namespace App\Filament\Resources\HippodromeResource\Pages;

use App\Filament\Resources\HippodromeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHippodromes extends ListRecords
{
    protected static string $resource = HippodromeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
