<?php

namespace App\Filament\Resources\HippodromeResource\Pages;

use App\Filament\Resources\HippodromeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHippodrome extends CreateRecord
{
    protected static string $resource = HippodromeResource::class;

    // Kayıt işleminden sonra listeye yönlendirmek isterseniz:
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
