<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Abonelikler';
    protected static ?string $modelLabel = 'Abonelik';
    protected static ?string $pluralModelLabel = 'Abonelikler';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Abonelik Bilgileri')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Kullanıcı')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('subscription_plan_id')
                            ->label('Abonelik Planı')
                            ->relationship('plan', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Durum')
                            ->options([
                                'pending' => 'Beklemede',
                                'active' => 'Aktif',
                                'expired' => 'Süresi Dolmuş',
                                'cancelled' => 'İptal Edildi',
                            ])
                            ->required()
                            ->default('pending'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Tarihler')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Başlangıç Tarihi')
                            ->required(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Bitiş Tarihi')
                            ->required(),

                        Forms\Components\DateTimePicker::make('cancelled_at')
                            ->label('İptal Tarihi')
                            ->nullable(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'active',
                        'danger' => 'expired',
                        'warning' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Beklemede',
                        'active' => 'Aktif',
                        'expired' => 'Süresi Dolmuş',
                        'cancelled' => 'İptal Edildi',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Başlangıç')
                    ->dateTime('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Bitiş')
                    ->dateTime('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remainingDays')
                    ->label('Kalan Gün')
                    ->getStateUsing(function ($record) {
                        $days = $record->remainingDays();
                        if ($days === null) return '-';
                        return $days > 0 ? $days . ' gün' : 'Süresi doldu';
                    })
                    ->color(fn($record) => $record->remainingDays() > 7 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'pending' => 'Beklemede',
                        'active' => 'Aktif',
                        'expired' => 'Süresi Dolmuş',
                        'cancelled' => 'İptal Edildi',
                    ]),

                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('cancel')
                    ->label('İptal Et')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Subscription $record) => $record->markAsCancelled())
                    ->visible(fn(Subscription $record) => $record->status === 'active'),

                Tables\Actions\Action::make('activate')
                    ->label('Aktifleştir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $record->update(['status' => 'active']);
                    })
                    ->visible(fn(Subscription $record) => $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
            'view' => Pages\ViewSubscription::route('/{record}'),
        ];
    }
    }
