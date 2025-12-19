<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use Filament\Actions;
use Filament\Notifications\Notification;

// <-- Yeni Bildirim Sınıfı
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggleHandled')
                ->label(fn(): string => $this->record->handled ? 'İşlem Kaldır' : 'İşaretle: İşlendi')
                ->color(fn(): string => $this->record->handled ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    // Durumu güncelle
                    $this->record->update(['handled' => !$this->record->handled]);

                    // Filament V3 Bildirimi
                    Notification::make()
                        ->title($this->record->handled ? 'İşlendi olarak işaretlendi.' : 'İşlem kaldırıldı.')
                        ->success()
                        ->send();

                    // Sayfayı yenile (Veriler güncellensin diye)
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                })
                ->visible(fn(): bool => true),
        ];
    }
}
