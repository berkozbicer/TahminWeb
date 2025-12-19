<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Paketler';
    protected static ?string $modelLabel = 'Paket';
    protected static ?string $pluralModelLabel = 'Abonelik Paketleri';
    protected static ?string $navigationGroup = 'Finans';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Paket Detayları')
                            ->description('Fiyatlandırma ve temel bilgiler')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Paket Adı')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        // İsme göre otomatik slug oluştur
                                        $set('slug', Str::slug($state));
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->label('URL Yolu (Slug)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Fiyat')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₺')
                                            ->maxValue(999999.99),

                                        Forms\Components\TextInput::make('duration_days')
                                            ->label('Süre (Gün)')
                                            ->required()
                                            ->numeric()
                                            ->suffix('Gün')
                                            ->minValue(1)
                                            ->default(30),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->label('Kısa Açıklama')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Paket Özellikleri')
                            ->description('Satış sayfasında madde madde görünecek özellikler')
                            ->schema([
                                Forms\Components\Repeater::make('features')
                                    ->label('Özellik Listesi')
                                    ->simple(
                                        Forms\Components\TextInput::make('feature')
                                            ->required()
                                            ->placeholder('Örn: Detaylı Analiz Erişimi')
                                    )
                                    ->addActionLabel('Yeni Özellik Ekle')
                                    ->reorderableWithButtons()
                                    ->grid(2),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Durum')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Satışa Açık')
                                    ->helperText('Bu paketi kullanıcılar satın alabilir mi?')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger'),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Paket Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Süre')
                    ->formatStateUsing(fn ($state) => $state . ' Gün')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Toplam Satış')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktiflik Durumu'),
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
            ->defaultSort('price', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
