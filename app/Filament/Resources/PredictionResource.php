<?php

namespace App\Filament\Resources;

use App\Models\Prediction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\PredictionResource\Pages;

class PredictionResource extends Resource
{
    protected static ?string $model = Prediction::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Tahminler';
    protected static ?string $modelLabel = 'Tahmin';
    protected static ?string $pluralModelLabel = 'Tahminler';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Yarış Bilgileri')
                    ->schema([
                        Forms\Components\Select::make('hippodrome_id')
                            ->label('Hipodrom')
                            ->relationship('hippodrome', 'name')
                            ->required()
                            ->searchable()
                            ->preload(), // GÜNCELLEME: Listeyi hızlı yükler

                        Forms\Components\DatePicker::make('race_date')
                            ->label('Yarış Tarihi')
                            ->required()
                            ->native(false),

                        Forms\Components\TimePicker::make('race_time')
                            ->label('Yarış Saati')
                            ->seconds(false),

                        Forms\Components\TextInput::make('race_number')
                            ->label('Koşu Numarası')
                            ->required()
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\TextInput::make('race_title')
                            ->label('Koşu Başlığı')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Erişim ve Durum')
                    ->schema([
                        Forms\Components\Select::make('access_level')
                            ->label('Erişim Seviyesi')
                            ->options([
                                'standard' => 'Standart Üyelik',
                                'premium' => 'Premium Üyelik',
                            ])
                            ->required()
                            ->default('standard'),

                        Forms\Components\Select::make('status')
                            ->label('Durum')
                            ->options([
                                'draft' => 'Taslak',
                                'published' => 'Yayında',
                            ])
                            ->required()
                            ->default('draft')
                            ->live(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Yayın Tarihi')
                            ->visible(fn(Forms\Get $get) => $get('status') === 'published'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Tahmin İçeriği')
                    ->schema([
                        Forms\Components\Textarea::make('basic_prediction')
                            ->label('Basit Tahmin (Standart Üyelik)')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Standart üyelerin görebileceği temel tahmin'),

                        Forms\Components\Textarea::make('detailed_analysis')
                            ->label('Detaylı Analiz (Premium Üyelik)')
                            ->rows(6)
                            ->columnSpanFull()
                            ->helperText('Premium üyelere özel detaylı analiz'),

                        Forms\Components\Textarea::make('banker_tips')
                            ->label('Banko İpuçları (Premium)')
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText('Premium üyelere özel banko tahminler'),

                        Forms\Components\KeyValue::make('statistics')
                            ->label('İstatistikler')
                            ->keyLabel('Özellik')
                            ->valueLabel('Değer')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Sonuç Bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('winning_horse')
                            ->label('Kazanan At'),

                        Forms\Components\TextInput::make('winning_odds')
                            ->label('Kazanan Oran')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\Select::make('prediction_result')
                            ->label('Tahmin Sonucu')
                            ->options([
                                'pending' => 'Beklemede',
                                'won' => 'Kazandı',
                                'lost' => 'Kaybetti',
                            ])
                            ->default('pending'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hippodrome.name')
                    ->label('Hipodrom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('race_date')
                    ->label('Tarih')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('race_time')
                    ->label('Saat')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('race_number')
                    ->label('Koşu')
                    ->numeric()
                    ->prefix('Koşu ') // GÜNCELLEME: "Koşu 1" şeklinde görünür
                    ->sortable(),

                Tables\Columns\TextColumn::make('access_level')
                    ->label('Seviye')
                    ->badge()
                    ->colors([
                        'gray' => 'standard',
                        'warning' => 'premium',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'standard' => 'Standart',
                        'premium' => 'Premium',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Taslak',
                        'published' => 'Yayında',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('prediction_result')
                    ->label('Sonuç')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'won',
                        'danger' => 'lost',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Beklemede',
                        'won' => 'Kazandı',
                        'lost' => 'Kaybetti',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hippodrome')
                    ->relationship('hippodrome', 'name'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('access_level'),
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
            ->defaultSort('race_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPredictions::route('/'),
            'create' => Pages\CreatePrediction::route('/create'),
            'edit' => Pages\EditPrediction::route('/{record}/edit'),
        ];
    }
}
