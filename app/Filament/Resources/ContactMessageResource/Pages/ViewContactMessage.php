<?php

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
                Actions\Action::make('toggleHandled')
                    ->label(fn (): string => $this->record->handled ? 'İşlem Kaldır' : 'İşaretle: İşlendi')
                    ->color(fn (): string => $this->record->handled ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->record->update(['handled' => ! $this->record->handled]);
                        $this->notify('success', $this->record->handled ? 'İşlendi olarak işaretlendi.' : 'İşlem kaldırıldı.');
                        // stay on the same view so the Placeholder updates
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    })
                    ->visible(fn (): bool => true),
        ];
    }
}
