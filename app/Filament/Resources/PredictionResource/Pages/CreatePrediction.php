<?php

declare(strict_types=1);

namespace App\Filament\Resources\PredictionResource\Pages;

use App\Filament\Resources\PredictionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrediction extends CreateRecord
{
    protected static string $resource = PredictionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
