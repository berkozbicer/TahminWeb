<?php

declare(strict_types=1);

namespace App\Filament\Resources\HippodromeResource\Pages;

use App\Filament\Resources\HippodromeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHippodrome extends CreateRecord
{
    protected static string $resource = HippodromeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
