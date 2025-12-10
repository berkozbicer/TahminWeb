<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'İletişim Mesajları';
    protected static ?int $navigationSort = 80;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Card::make()->schema([
                TextInput::make('id')->label('ID')->disabled(),
                TextInput::make('name')->label('İsim')->disabled(),
                TextInput::make('email')->label('E-posta')->disabled(),
                Textarea::make('message')->label('Mesaj')->disabled(),
                Placeholder::make('handled')
                    ->label('İşlendi')
                    ->content(fn (?\App\Models\ContactMessage $record): string => $record && $record->handled ? 'Evet' : 'Hayır'),
                Placeholder::make('created_at')
                    ->label('Tarih')
                    ->content(fn (?\App\Models\ContactMessage $record): string => $record ? $record->created_at?->format('Y-m-d H:i') : ''),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('name')->label('İsim')->searchable()->sortable(),
                TextColumn::make('email')->label('E-posta')->searchable()->sortable()->copyable(),
                TextColumn::make('message')->label('Mesaj')->limit(60),
                TextColumn::make('created_at')->label('Tarih')->dateTime('short')->sortable(),
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
                    ->label('İşaretle: İşlendi')
                    ->action(function (ContactMessage $record) {
                        $record->update(['handled' => true]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => !$record->handled),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markHandled')
                    ->label('Seçilileri İşlendi Yap')
                    ->action(fn($records) => $records->each->update(['handled' => true]))
                    ->requiresConfirmation(),
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
