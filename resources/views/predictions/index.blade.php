@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Başlık ve Filtreler -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-secondary mb-4">Tüm Tahminler</h1>

            <!-- Filtre Formu -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form method="GET" action="{{ route('predictions.index') }}"
                      class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Hipodrom Filtresi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hipodrom</label>
                        <select name="hippodrome"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">Tüm Hipodromlar</option>
                            @foreach($hippodromes as $hippodrome)
                                <option
                                    value="{{ $hippodrome->id }}" {{ request('hippodrome') == $hippodrome->id ? 'selected' : '' }}>
                                    {{ $hippodrome->name }} ({{ $hippodrome->city }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tarih Filtresi -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tarih</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <!-- Filtrele Butonu -->
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full bg-primary hover:bg-red-700 text-white py-2 px-6 rounded-lg font-semibold transition">
                            Filtrele
                        </button>
                    </div>
                </form>

                <!-- Filtreyi Temizle -->
                @if(request()->has('hippodrome') || request()->has('date'))
                    <div class="mt-4">
                        <a href="{{ route('predictions.index') }}"
                           class="text-primary hover:text-red-700 font-semibold text-sm">
                            ✕ Filtreleri Temizle
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tahmin Kartları -->
        @if($predictions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($predictions as $prediction)
                    <div
                        class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition border-t-4 {{ $prediction->access_level === 'premium' ? 'border-primary' : 'border-gray-600' }}">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-lg text-secondary">{{ $prediction->hippodrome->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $prediction->hippodrome->city }}</p>
                                </div>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold {{ $prediction->access_level === 'premium' ? 'bg-red-100 text-primary' : 'bg-gray-100 text-gray-700' }}">
                                {{ $prediction->access_level === 'premium' ? 'PREMIUM' : 'STANDART' }}
                            </span>
                            </div>

                            <!-- Yarış Bilgileri -->
                            <div class="mb-4 pb-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-3xl font-bold text-primary">{{ $prediction->race_number }}.
                                            Koşu
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $prediction->race_date
                                                ? \Carbon\Carbon::parse($prediction->race_date)->format('d.m.Y')
                                                : '-' }}

                                            -
                                            {{ $prediction->race_time
                                                ? \Carbon\Carbon::parse($prediction->race_time)->format('H:i')
                                                : '-' }}
                                        </div>
                                    </div>
                                    @if($prediction->prediction_result !== 'pending')
                                        <div>
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold {{ $prediction->getResultBadgeColor() }} text-white">
                                            {{ $prediction->prediction_result === 'won' ? '✓ TUTTU' : '✗ TUTMADI' }}
                                        </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Önizleme -->
                            <p class="text-gray-700 text-sm mb-4 line-clamp-3">
                                {{ Str::limit($prediction->basic_prediction, 120) }}
                            </p>

                            <!-- Detay Butonu -->
                            <a href="{{ route('predictions.show', $prediction) }}"
                               class="block w-full text-center bg-primary hover:bg-red-700 text-white py-2 rounded-lg font-semibold transition">
                                Detayları Gör
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $predictions->links() }}
            </div>

        @else
            <!-- Sonuç Bulunamadı -->
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-2xl font-bold text-secondary mb-2">Tahmin Bulunamadı</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->has('hippodrome') || request()->has('date'))
                        Seçtiğiniz filtrelere uygun tahmin bulunmamaktadır.
                    @else
                        Henüz yayınlanmış tahmin bulunmamaktadır.
                    @endif
                </p>
                @if(request()->has('hippodrome') || request()->has('date'))
                    <a href="{{ route('predictions.index') }}"
                       class="inline-block bg-primary hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        Tüm Tahminleri Gör
                    </a>
                @endif
            </div>
        @endif

        <!-- Bilgilendirme Kutusu -->
        @guest
            <div class="mt-8 bg-gradient-to-r from-primary to-secondary rounded-xl shadow-lg p-8 text-white text-center">
                <h3 class="text-2xl font-bold mb-4">Tüm Tahminlere Erişin!</h3>
                <p class="mb-6">Üye olarak detaylı analizler ve banko kuponlara ulaşabilirsiniz.</p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="bg-white text-primary hover:bg-gray-100 px-6 py-3 rounded-lg font-bold transition">
                        Hemen Kayıt Ol
                    </a>
                    <a href="{{ route('subscriptions.index') }}"
                       class="bg-transparent border-2 border-white hover:bg-white hover:text-primary px-6 py-3 rounded-lg font-bold transition">
                        Paketleri İncele
                    </a>
                </div>
            </div>
        @endguest
    </div>
@endsection
