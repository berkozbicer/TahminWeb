<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;

// <-- Card yerine Section (V3 Standardı)
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

// <-- Bildirim için
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'İletişim Mesajları';
    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Mesaj Detayları')->schema([ // Card -> Section
                TextInput::make('id')
                    ->label('ID')
                    ->disabled(),
                TextInput::make('name')
                    ->label('İsim')
                    ->disabled(),
                TextInput::make('email')
                    ->label('E-posta')
                    ->disabled(),
                Textarea::make('message')
                    ->label('Mesaj')
                    ->disabled()
                    ->columnSpanFull(), // Mesaj alanı tam genişlik olsun
                Placeholder::make('handled')
                    ->label('İşlendi')
                    ->content(fn(?ContactMessage $record): string => $record && $record->handled ? 'Evet' : 'Hayır'),
                Placeholder::make('created_at')
                    ->label('Tarih')
                    ->content(fn(?ContactMessage $record): string => $record ? $record->created_at?->format('d.m.Y H:i') : ''),
            ])->columns(2), // Görünümü 2 kolon yaptım, daha derli toplu durur
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('İsim')->searchable()->sortable(),
                TextColumn::make('email')->label('E-posta')->searchable()->sortable()->copyable(),
                TextColumn::make('message')->label('Mesaj')->limit(50),
                TextColumn::make('created_at')->label('Tarih')->dateTime('d.m.Y H:i')->sortable(),
                IconColumn::make('handled')->label('İşlendi')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('handled')
                    ->label('İşlenme Durumu')
                    ->options([
                        '0' => 'İşlenmedi',
                        '1' => 'İşlendi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('markHandled')
                    ->label('İşlendi İşaretle')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (ContactMessage $record) {
                        $record->update(['handled' => true]);

                        Notification::make()
                            ->title('Mesaj işlendi olarak işaretlendi')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => !$record->handled),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markHandled')
                    ->label('Seçilileri İşlendi Yap')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function ($records) {
                        $records->each->update(['handled' => true]);

                        Notification::make()
                            ->title('Seçilen mesajlar işlendi olarak güncellendi')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
