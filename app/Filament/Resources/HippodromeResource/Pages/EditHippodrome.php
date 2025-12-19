<?php

declare(strict_types=1);

namespace App\Filament\Resources\HippodromeResource\Pages;

use App\Filament\Resources\HippodromeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHippodrome extends EditRecord
{
    protected static string $resource = HippodromeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
