<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\HippodromeResource\Pages;
use App\Models\Hippodrome;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HippodromeResource extends Resource
{
    protected static ?string $model = Hippodrome::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Hipodromlar';
    protected static ?string $modelLabel = 'Hipodrom';
    protected static ?string $pluralModelLabel = 'Hipodromlar';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form elemanlarını Section içine aldık
                Section::make('Hipodrom Bilgileri')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Hipodrom Adı')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('city')
                        ->label('Şehir')
                        ->maxLength(255)
                        ->columnSpan(1),
                ])->columns(2), // Yan yana 2 sütun
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Hipodrom Adı')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('Şehir')
                    ->searchable()
                    ->sortable(), // Şehre göre de sıralama eklendi

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ekleme Tarihi')
                    ->dateTime('d.m.Y H:i') // Saat detayını da ekledim
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // İstenirse tablodan gizlenebilsin
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc'); // Varsayılan olarak isme göre sıralı gelsin
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHippodromes::route('/'),
            'create' => Pages\CreateHippodrome::route('/create'),
            'edit' => Pages\EditHippodrome::route('/{record}/edit'),
        ];
    }
}
