@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <div class="mb-6 text-sm text-gray-600">
            <a href="/" class="hover:text-primary">Ana Sayfa</a> /
            <a href="/tahminler" class="hover:text-primary">Tahminler</a> /
            <span class="text-gray-900">{{ $prediction->hippodrome->name }}</span>
        </div>

        <!-- Ana Kart -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-secondary to-gray-800 text-white p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">{{ $prediction->race_title ?? $prediction->race_number . '. Koşu' }}</h1>
                        <p class="text-gray-300">{{ $prediction->hippodrome->name }}
                            - {{ $prediction->hippodrome->city }}</p>
                    </div>
                    <span
                        class="px-4 py-2 rounded-full text-sm font-bold {{ $prediction->access_level === 'premium' ? 'bg-red-600' : 'bg-gray-600' }}">
                    {{ $prediction->access_level === 'premium' ? 'PREMIUM' : 'STANDART' }}
                </span>
                </div>

                <div class="flex space-x-6 text-sm">

                    <div>
                        <span class="text-gray-400">Tarih:</span>
                        <span class="font-semibold">
                            {{ $prediction->race_date
                            ? \Illuminate\Support\Carbon::parse($prediction->race_date)->format('d.m.Y')
                            : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Saat:</span>
                        <span class="font-semibold">
                            {{ !empty($prediction->race_time) && $prediction->race_time !== '-'
                            ? \Illuminate\Support\Carbon::parse($prediction->race_time)->format('H:i')
                            : '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Koşu No:</span>
                        <span class="font-semibold">{{ $prediction->race_number }}</span>
                    </div>
                </div>
            </div>

            <!-- İçerik -->
            <div class="p-6">
                @if($canAccess)
                    <!-- Basit Tahmin -->
                    @if($prediction->basic_prediction)
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-secondary mb-4 flex items-center">
                                <span
                                    class="bg-primary text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">1</span>
                                Genel Tahmin
                            </h2>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $prediction->basic_prediction }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Detaylı Analiz (Sadece Premium) -->
                    @if($prediction->detailed_analysis && $prediction->isPremiumOnly())
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-secondary mb-4 flex items-center">
                                <span
                                    class="bg-primary text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">2</span>
                                Detaylı Analiz
                                <span class="ml-3 px-3 py-1 bg-red-100 text-primary text-xs font-semibold rounded-full">PREMIUM</span>
                            </h2>
                            <div
                                class="bg-gradient-to-br from-red-50 to-white rounded-lg p-6 border-l-4 border-primary">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $prediction->detailed_analysis }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Banko İpuçları (Sadece Premium) -->
                    @if($prediction->banker_tips && $prediction->isPremiumOnly())
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-secondary mb-4 flex items-center">
                                <span
                                    class="bg-yellow-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">★</span>
                                Banko İpuçları
                                <span class="ml-3 px-3 py-1 bg-red-100 text-primary text-xs font-semibold rounded-full">PREMIUM</span>
                            </h2>
                            <div class="bg-yellow-50 rounded-lg p-6 border-l-4 border-yellow-500">
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line font-semibold">{{ $prediction->banker_tips }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- İstatistikler -->
                    @if($prediction->statistics && count($prediction->statistics) > 0)
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-secondary mb-4 flex items-center">
                                <span
                                    class="bg-primary text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">📊</span>
                                İstatistikler
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($prediction->statistics as $key => $value)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div
                                            class="text-sm text-gray-600 mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                                        <div class="text-lg font-bold text-secondary">{{ $value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Sonuç (Eğer varsa) -->
                    @if($prediction->winning_horse)
                        <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6">
                            <h3 class="text-xl font-bold text-green-800 mb-2">🏆 Yarış Sonucu</h3>
                            <p class="text-green-700">
                                <strong>Kazanan:</strong> {{ $prediction->winning_horse }}
                                @if($prediction->winning_odds)
                                    <span class="ml-4"><strong>Oran:</strong> {{ $prediction->winning_odds }}</span>
                                @endif
                            </p>
                            @if($prediction->prediction_result === 'won')
                                <span class="inline-block mt-2 px-4 py-2 bg-green-600 text-white rounded-lg font-bold">✓ TAHMİN TUTTU!</span>
                            @endif
                        </div>
                    @endif

                @else
                    <!-- Erişim Yok -->
                    <div class="text-center py-12">
                        @if($needsUpgrade)
                            <div class="bg-red-50 rounded-xl p-8 max-w-2xl mx-auto">
                                <div class="text-6xl mb-4">🔒</div>
                                <h2 class="text-3xl font-bold text-secondary mb-4">Premium İçerik</h2>
                                <p class="text-gray-700 mb-6">
                                    Bu detaylı analiz ve banko ipuçları sadece <strong>Premium</strong> üyelere özeldir.
                                </p>
                                <a href="{{ route('subscriptions.upgrade') }}"
                                   class="inline-block bg-primary hover:bg-red-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105">
                                    Premium'a Yükselt →
                                </a>
                            </div>
                        @else
                            <div class="bg-gray-50 rounded-xl p-8 max-w-2xl mx-auto">
                                <div class="text-6xl mb-4">🔐</div>
                                <h2 class="text-3xl font-bold text-secondary mb-4">Abonelik Gerekli</h2>
                                <p class="text-gray-700 mb-6">
                                    Bu tahminleri görüntülemek için aktif bir aboneliğiniz olmalıdır.
                                </p>
                                <a href="{{ route('subscriptions.index') }}"
                                   class="inline-block bg-primary hover:bg-red-700 text-white px-8 py-4 rounded-lg font-bold text-lg transition transform hover:scale-105">
                                    Abonelik Paketlerini İncele →
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Diğer Tahminler -->
        <div class="mt-8">
            <h3 class="text-2xl font-bold text-secondary mb-4">Diğer Tahminler</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                @foreach($relatedPredictions as $related)
                    <a href="{{ route('predictions.show', $related) }}"
                       class="block bg-white rounded-lg shadow hover:shadow-lg transition p-4">
                        <div class="font-bold text-secondary">{{ $related->hippodrome->name }}</div>
                        <div class="text-sm text-gray-600">
                            {{ $related->race_number }}. Koşu -
                            {{ $related->race_time ? \Carbon\Carbon::parse($related->race_time)->format('H:i') : '-' }}
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
