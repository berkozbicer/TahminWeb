<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Abonelikler';
    protected static ?string $modelLabel = 'Abonelik';
    protected static ?string $pluralModelLabel = 'Abonelikler';
    protected static ?string $navigationGroup = 'Finans';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Abonelik Detayları')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Kullanıcı')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')->required(),
                                        Forms\Components\TextInput::make('email')->required()->email(),
                                        Forms\Components\TextInput::make('password')->password()->required(),
                                    ]),

                                Forms\Components\Select::make('subscription_plan_id')
                                    ->label('Plan Seçimi')
                                    ->relationship('plan', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if (!$state) return;

                                        $plan = SubscriptionPlan::find($state);
                                        if ($plan) {
                                            $days = $plan->duration_days ?? 30;
                                            $set('started_at', now()->toDateTimeString());
                                            $set('expires_at', now()->addDays($days)->toDateTimeString());
                                            $set('status', 'active');
                                        }
                                    }),

                                Forms\Components\Select::make('status')
                                    ->label('Durum')
                                    ->options([
                                        'active' => 'Aktif ✅',
                                        'pending' => 'Beklemede ⏳',
                                        'expired' => 'Süresi Doldu 🛑',
                                        'cancelled' => 'İptal Edildi 🚫',
                                    ])
                                    ->required()
                                    ->default('active'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Zamanlama')
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Başlangıç')
                                    ->required()
                                    ->default(now()),

                                Forms\Components\DateTimePicker::make('expires_at')
                                    ->label('Bitiş')
                                    ->required()
                                    ->native(false),
                                Forms\Components\DateTimePicker::make('cancelled_at')
                                    ->label('İptal Tarihi')
                                    ->visible(fn(Get $get) => $get('status') === 'cancelled')
                                    ->disabled(),
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->description(fn(Subscription $record) => $record->user?->email)
                    ->searchable(['name', 'email'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remainingDays')
                    ->label('Kalan Süre')
                    ->getStateUsing(function (Subscription $record) {
                        if ($record->status !== 'active') return null;
                        return $record->remainingDays();
                    })
                    ->formatStateUsing(fn($state) => $state . ' Gün')
                    ->badge()
                    ->color(fn($state) => $state < 3 ? 'danger' : ($state < 7 ? 'warning' : 'success'))
                    ->icon('heroicon-o-clock'),

                // Durum
                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'gray',
                        'expired' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'pending' => 'Beklemede',
                        'expired' => 'Bitti',
                        'cancelled' => 'İptal',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('dates')
                    ->label('Tarih Aralığı')
                    ->getStateUsing(fn(Subscription $record) => $record->started_at->format('d.m.Y') . ' - ' . $record->expires_at->format('d.m.Y')
                    )
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'pending' => 'Beklemede',
                        'expired' => 'Süresi Dolmuş',
                        'cancelled' => 'İptal Edildi',
                    ]),

                Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name'),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('Süresi Dolmak Üzere (< 3 Gün)')
                    ->query(fn(Builder $query) => $query
                        ->where('status', 'active')
                        ->where('expires_at', '<=', now()->addDays(3))
                        ->where('expires_at', '>', now())
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('extend')
                        ->label('Süreyi Uzat (+7 Gün)')
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->visible(fn(Subscription $record) => $record->status === 'active')
                        ->action(function (Subscription $record) {
                            $record->update([
                                'expires_at' => $record->expires_at->addDays(7)
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Süre Uzatıldı')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('cancel')
                        ->label('İptal Et')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(Subscription $record) => $record->status === 'active')
                        ->action(fn(Subscription $record) => $record->markAsCancelled()),
                ])
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
