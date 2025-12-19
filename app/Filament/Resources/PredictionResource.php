<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PredictionResource\Pages;
use App\Models\Prediction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PredictionResource extends Resource
{
    protected static ?string $model = Prediction::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationLabel = 'Tahminler';
    protected static ?string $modelLabel = 'Tahmin';
    protected static ?string $pluralModelLabel = 'Tahminler';
    protected static ?string $navigationGroup = 'Yönetim';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Yarış Detayları')
                            ->description('Hangi hipodrom, hangi koşu?')
                            ->schema([
                                Forms\Components\Select::make('hippodrome_id')
                                    ->label('Hipodrom')
                                    ->relationship('hippodrome', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\DatePicker::make('race_date')
                                    ->label('Yarış Tarihi')
                                    ->required()
                                    ->default(now())
                                    ->native(false),

                                Forms\Components\TimePicker::make('race_time')
                                    ->label('Yarış Saati')
                                    ->seconds(false)
                                    ->required(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('race_number')
                                            ->label('Koşu No')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1),

                                        Forms\Components\TextInput::make('race_title')
                                            ->label('Koşu Başlığı')
                                            ->placeholder('Örn: Handikap 15')
                                            ->maxLength(255),
                                    ]),
                            ])->columns(2),

                        Forms\Components\Section::make('Tahmin İçeriği')
                            ->schema([
                                Forms\Components\Textarea::make('basic_prediction')
                                    ->label('Basit Tahmin (Standart)')
                                    ->rows(3)
                                    ->placeholder('Kısa özet...')
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('detailed_analysis')
                                    ->label('Detaylı Analiz (Premium)')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'bulletList', 'orderedList', 'undo', 'redo'
                                    ])
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('banker_tips')
                                    ->label('Banko / Sürpriz (Premium)')
                                    ->toolbarButtons(['bold', 'italic'])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Yayın Ayarları')
                            ->schema([
                                Forms\Components\Select::make('access_level')
                                    ->label('Erişim Seviyesi')
                                    ->options([
                                        'standard' => 'Standart',
                                        'premium' => 'Premium ⭐',
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
                                    ->live() // Anlık değişimleri dinle
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state === 'published') {
                                            $set('published_at', now()->toDateTimeString());
                                        }
                                    }),

                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label('Yayınlanma Tarihi')
                                    ->native(false)
                                    ->visible(fn(Forms\Get $get) => $get('status') === 'published'),
                            ]),

                        Forms\Components\Section::make('Yarış Sonucu')
                            ->description('Yarış bittikten sonra doldurunuz.')
                            ->collapsed()
                            ->schema([
                                Forms\Components\Select::make('prediction_result')
                                    ->label('Sonuç')
                                    ->options([
                                        'pending' => 'Bekleniyor',
                                        'won' => 'Kazandı 🏆',
                                        'lost' => 'Kaybetti ❌',
                                    ])
                                    ->default('pending')
                                    ->selectablePlaceholder(false),

                                Forms\Components\TextInput::make('winning_horse')
                                    ->label('Kazanan At'),

                                Forms\Components\TextInput::make('winning_odds')
                                    ->label('Ganyan')
                                    ->numeric()
                                    ->suffix('TL')
                                    ->step(0.05),
                            ]),

                        Forms\Components\Section::make('Meta Veriler')
                            ->collapsed()
                            ->schema([
                                Forms\Components\KeyValue::make('statistics')
                                    ->label('Ek İstatistikler')
                                    ->keyLabel('Veri Adı')
                                    ->valueLabel('Değer')
                                    ->reorderable(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('race_date')
                    ->label('Tarih')
                    ->date('d.m.Y')
                    ->sortable()
                    ->description(fn(Prediction $record) => $record->race_time),

                Tables\Columns\TextColumn::make('hippodrome.name')
                    ->label('Hipodrom')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin'),

                Tables\Columns\TextColumn::make('race_number')
                    ->label('Koşu')
                    ->formatStateUsing(fn($state) => "{$state}. Koşu")
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('access_level')
                    ->label('Seviye')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'standard' => 'gray',
                        'premium' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'standard' => 'Standart',
                        'premium' => 'Premium',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Taslak',
                        'published' => 'Yayında',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('prediction_result')
                    ->label('Sonuç')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'info',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): ?string => match ($state) {
                        'won' => 'heroicon-o-check-circle',
                        'lost' => 'heroicon-o-x-circle',
                        default => null,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hippodrome')
                    ->relationship('hippodrome', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Yayın Durumu')
                    ->options([
                        'draft' => 'Taslak',
                        'published' => 'Yayında',
                    ]),

                Tables\Filters\Filter::make('today')
                    ->label('Bugünün Yarışları')
                    ->query(fn(Builder $query): Builder => $query->whereDate('race_date', now())),
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
            ->defaultSort('race_date', 'desc')
            ->poll('60s');
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
