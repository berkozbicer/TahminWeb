<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Kullanıcılar';
    protected static ?string $modelLabel = 'Kullanıcı';
    protected static ?string $pluralModelLabel = 'Kullanıcılar';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Yönetim';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Kullanıcı Kimliği')
                    ->description('Temel hesap bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Ad Soyad')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-m-user'),

                        Forms\Components\TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-m-at-symbol'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20)
                            ->prefixIcon('heroicon-m-phone'),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('E-posta Doğrulama Tarihi')
                            ->placeholder('Doğrulanmadı'),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Erişim Ayarları')
                            ->schema([
                                Forms\Components\Select::make('role')
                                    ->label('Rol')
                                    ->options([
                                        'user' => 'Kullanıcı',
                                        'admin' => 'Yönetici 🛡️',
                                    ])
                                    ->required()
                                    ->default('user')
                                    ->selectablePlaceholder(false),
                            ]),

                        Forms\Components\Section::make('Güvenlik')
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Şifre')
                                    ->password()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->maxLength(255)
                                    ->confirmed()
                                    ->autocomplete('new-password'),

                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Şifre Tekrar')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->required(fn(string $context): bool => $context === 'create'),
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
                    ->label('Ad Soyad')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('E-posta kopyalandı')
                    ->icon('heroicon-m-envelope'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Onay')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn($record) => $record->email_verified_at ? $record->email_verified_at->format('d.m.Y H:i') : 'Doğrulanmadı'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'user' => 'gray',
                        'admin' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'user' => 'Kullanıcı',
                        'admin' => 'Yönetici',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('activeSubscription.plan.name')
                    ->label('Abonelik')
                    ->badge()
                    ->color('success')
                    ->placeholder('Yok')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kayıt')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'user' => 'Kullanıcı',
                        'admin' => 'Yönetici',
                    ]),

                Tables\Filters\Filter::make('has_active_subscription')
                    ->label('Aktif Aboneliği Olanlar')
                    ->query(fn(Builder $query) => $query->whereHas('activeSubscription')),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('E-posta Onayı')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('verify_email')
                        ->label('E-postayı Onayla')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn(User $record) => $record->email_verified_at === null)
                        ->action(function (User $record) {
                            $record->forceFill(['email_verified_at' => now()])->save();
                            \Filament\Notifications\Notification::make()
                                ->title('Kullanıcı doğrulandı')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
