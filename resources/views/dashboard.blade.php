@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-secondary mb-1">Hoş geldin, {{ $user->name }}! 👋</h2>
                    <p class="text-gray-600">At yarışı tahmin panelindesin. Buradan durumunu kontrol edebilirsin.</p>
                </div>
                <div class="hidden sm:block">
                    <img src="{{ asset('images/hipodrom_casusu_logo.png') }}" alt="Hipodrom Casusu" class="h-16 w-auto">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-primary relative overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Mevcut Paket</h3>
                    <div class="bg-red-50 p-2 rounded-full">
                        <i class="fas fa-crown text-primary text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-secondary">
                    {{ $activeSubscription ? $activeSubscription->plan->name : 'Paket Yok' }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $activeSubscription ? 'Aktif Abonelik' : 'Ücretsiz Üyelik' }}
                </p>
            </div>

            <div
                class="bg-white rounded-xl shadow p-6 border-l-4 {{ $activeSubscription ? 'border-green-500' : 'border-gray-300' }}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Kalan Süre</h3>
                    <div class="bg-green-50 p-2 rounded-full">
                        <i class="fas fa-hourglass-half text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-2xl font-bold text-secondary">
                    @if($activeSubscription && $activeSubscription->expires_at)
                        {{ $activeSubscription->expires_at->diffInDays(now()) }} Gün
                    @else
                        -
                    @endif
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    @if($activeSubscription && $activeSubscription->expires_at)
                        Bitiş: {{ $activeSubscription->expires_at->format('d.m.Y') }}
                    @else
                        Süresiz Erişim
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Abonelik Durumu</h3>
                    <div class="bg-blue-50 p-2 rounded-full">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                </div>

                @if($activeSubscription && $activeSubscription->isActive())
                    <div class="flex items-center justify-between">
                        <span class="bg-green-100 text-green-800 text-sm font-bold px-3 py-1 rounded-full">
                            ✅ Aktif
                        </span>
                        <a href="{{ route('subscriptions.index') }}"
                           class="text-sm text-primary hover:underline font-semibold">
                            Değiştir
                        </a>
                    </div>
                @else
                    <div class="flex flex-col">
                        <span
                            class="inline-block w-max bg-gray-100 text-gray-600 text-sm font-bold px-3 py-1 rounded-full mb-2">
                            ❌ Pasif
                        </span>
                        <a href="{{ route('subscriptions.index') }}"
                           class="text-sm text-primary font-bold hover:underline">
                            Paket Satın Al →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
                <h3 class="text-xl font-bold text-secondary mb-6 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-gray-400"></i> Profil Bilgileri
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500 font-medium">Ad Soyad</span>
                        <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500 font-medium">E-posta</span>
                        <span class="font-semibold text-gray-800">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500 font-medium">Telefon</span>
                        <span class="font-semibold text-gray-800">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-3">
                        <span class="text-gray-500 font-medium">Kayıt Tarihi</span>
                        <span class="font-semibold text-gray-800">{{ $user->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <a href="{{ route('profile.edit') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                        Profili Düzenle
                    </a>
                </div>
            </div>

            <div
                class="bg-gradient-to-br from-secondary to-gray-900 rounded-xl shadow p-6 text-white flex flex-col justify-center text-center">
                <div class="mb-4">
                    <span class="text-4xl">🎯</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Günün Tahminleri</h3>
                <p class="text-gray-300 mb-6 text-sm">
                    Bugünkü yarışlar için uzman ekibimizin hazırladığı analizleri kaçırma!
                </p>
                <a href="{{ route('predictions.today') }}"
                   class="w-full bg-primary hover:bg-red-700 text-white py-3 rounded-lg font-bold transition shadow-lg">
                    Tahminlere Git
                </a>

                @if(!$activeSubscription)
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="text-xs text-gray-400 mb-2">Premium ayrıcalıklarını keşfet</p>
                        <a href="{{ route('subscriptions.index') }}"
                           class="text-primary hover:text-white transition text-sm font-semibold">
                            Paketleri İncele
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
